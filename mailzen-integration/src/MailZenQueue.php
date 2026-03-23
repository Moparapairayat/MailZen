<?php

namespace MailZenIntegration;

class MailZenQueue
{
    public static function register(): void
    {
        add_filter('cron_schedules', [self::class, 'addSchedules']);
        add_action(MAILZEN_INTEGRATION_CRON_HOOK, [self::class, 'processQueue']);

        if (!wp_next_scheduled(MAILZEN_INTEGRATION_CRON_HOOK)) {
            wp_schedule_event(time() + 60, 'mailzen_minute', MAILZEN_INTEGRATION_CRON_HOOK);
        }
    }

    public static function activate(): void
    {
        self::createTable();

        if (!wp_next_scheduled(MAILZEN_INTEGRATION_CRON_HOOK)) {
            wp_schedule_event(time() + 60, 'mailzen_minute', MAILZEN_INTEGRATION_CRON_HOOK);
        }
    }

    public static function deactivate(): void
    {
        $ts = wp_next_scheduled(MAILZEN_INTEGRATION_CRON_HOOK);
        if ($ts) {
            wp_unschedule_event($ts, MAILZEN_INTEGRATION_CRON_HOOK);
        }
    }

    public static function enqueue(array $payload, ?string $availableAtUtc = null): void
    {
        global $wpdb;

        $table = $wpdb->prefix . MAILZEN_INTEGRATION_QUEUE_TABLE;

        $availableAtUtc = $availableAtUtc ?: current_time('mysql', 1);

        $wpdb->insert(
            $table,
            [
                'status' => 'pending',
                'attempts' => 0,
                'available_at' => $availableAtUtc,
                'payload' => wp_json_encode($payload),
                'created_at' => current_time('mysql', 1),
                'updated_at' => current_time('mysql', 1),
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s']
        );
    }

    public static function enqueueDelayed(array $payload, int $delaySeconds): void
    {
        $delaySeconds = max(0, (int) $delaySeconds);
        $available = gmdate('Y-m-d H:i:s', time() + $delaySeconds);
        self::enqueue($payload, $available);
    }

    public static function processQueue(): void
    {
        global $wpdb;

        $settings = get_option(MAILZEN_INTEGRATION_OPTION_KEY, []);
        $client = MailZenClient::fromSettings(is_array($settings) ? $settings : []);
        if (!$client) {
            return;
        }

        if (empty($settings['signing_secret'])) {
            $sig = $client->fetchSigningSecret();
            if (($sig['ok'] ?? false) && is_array($sig['data']['data'] ?? null)) {
                $secret = trim((string) ($sig['data']['data']['signing_secret'] ?? ''));
                if ($secret !== '') {
                    $settings['signing_secret'] = $secret;
                    update_option(MAILZEN_INTEGRATION_OPTION_KEY, $settings, false);
                    $client = MailZenClient::fromSettings($settings) ?: $client;
                }
            }
        }

        $table = $wpdb->prefix . MAILZEN_INTEGRATION_QUEUE_TABLE;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE status = %s AND available_at <= %s ORDER BY id ASC LIMIT 25",
                'pending',
                current_time('mysql', 1)
            ),
            ARRAY_A
        );

        if (!is_array($rows) || empty($rows)) {
            return;
        }

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $claimed = $wpdb->update(
                $table,
                [
                    'status' => 'processing',
                    'updated_at' => current_time('mysql', 1),
                ],
                [
                    'id' => $id,
                    'status' => 'pending',
                ],
                ['%s', '%s'],
                ['%d', '%s']
            );

            if ($claimed !== 1) {
                continue;
            }

            $payload = [];
            $raw = (string) ($row['payload'] ?? '');
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            }

            if (($payload['event'] ?? '') === 'woo_abandoned_checkout') {
                $orderId = isset($payload['payload']['order_id']) ? (int) $payload['payload']['order_id'] : 0;
                if ($orderId > 0 && function_exists('wc_get_order')) {
                    $order = wc_get_order($orderId);
                    if ($order) {
                        $status = (string) $order->get_status();
                        $active = ['pending', 'failed', 'on-hold'];
                        if (!in_array($status, $active, true)) {
                            $wpdb->delete($table, ['id' => $id], ['%d']);
                            continue;
                        }
                    }
                }
            }

            $resp = $client->sendWordPressEvent($payload);

            if ($resp['ok'] ?? false) {
                $wpdb->delete($table, ['id' => $id], ['%d']);
                continue;
            }

            $status = (int) ($resp['status'] ?? 0);
            if ($status === 409) {
                $wpdb->delete($table, ['id' => $id], ['%d']);
                continue;
            }

            $attempts = (int) ($row['attempts'] ?? 0) + 1;
            $backoffSeconds = min(3600, 30 * $attempts);
            $nextAvailableAt = gmdate('Y-m-d H:i:s', time() + $backoffSeconds);

            $wpdb->update(
                $table,
                [
                    'status' => 'pending',
                    'attempts' => $attempts,
                    'available_at' => $nextAvailableAt,
                    'last_error' => isset($resp['error']) ? (string) $resp['error'] : null,
                    'updated_at' => current_time('mysql', 1),
                ],
                ['id' => $id],
                ['%s', '%d', '%s', '%s', '%s'],
                ['%d']
            );
        }
    }

    public static function addSchedules(array $schedules): array
    {
        if (!isset($schedules['mailzen_minute'])) {
            $schedules['mailzen_minute'] = [
                'interval' => 60,
                'display' => 'Every minute (MailZen)',
            ];
        }

        return $schedules;
    }

    private static function createTable(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . MAILZEN_INTEGRATION_QUEUE_TABLE;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            status VARCHAR(32) NOT NULL,
            attempts INT UNSIGNED NOT NULL DEFAULT 0,
            available_at DATETIME NOT NULL,
            payload LONGTEXT NOT NULL,
            last_error LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY status_available (status, available_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
