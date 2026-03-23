<?php

namespace MailZenIntegration;

class MailZenSettingsPage
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'adminMenu']);
        add_action('admin_init', [self::class, 'registerSettings']);
        add_action('admin_post_mailzen_integration_test', [self::class, 'handleTestConnection']);
    }

    public static function adminMenu(): void
    {
        add_options_page(
            'MailZen Integration',
            'MailZen',
            'manage_options',
            'mailzen-integration',
            [self::class, 'render']
        );
    }

    public static function registerSettings(): void
    {
        register_setting('mailzen_integration', MAILZEN_INTEGRATION_OPTION_KEY, [
            'type' => 'array',
            'sanitize_callback' => [self::class, 'sanitizeSettings'],
            'default' => [],
        ]);
    }

    public static function sanitizeSettings($value): array
    {
        $value = is_array($value) ? $value : [];

        $out = [];
        $out['base_url'] = isset($value['base_url']) ? esc_url_raw(trim((string) $value['base_url'])) : '';
        $out['api_key'] = isset($value['api_key']) ? trim((string) $value['api_key']) : '';
        $out['list_id'] = isset($value['list_id']) ? (int) $value['list_id'] : 0;
        $out['signing_secret'] = isset($value['signing_secret']) ? trim((string) $value['signing_secret']) : '';

        $events = isset($value['events']) && is_array($value['events']) ? $value['events'] : [];
        $defaults = json_decode(MAILZEN_INTEGRATION_DEFAULT_EVENTS, true);
        $defaults = is_array($defaults) ? $defaults : [];

        $outEvents = [];
        foreach (array_keys($defaults) as $k) {
            $outEvents[$k] = !empty($events[$k]);
        }

        $out['events'] = $outEvents;

        $eventListMap = isset($value['event_list_map']) && is_array($value['event_list_map']) ? $value['event_list_map'] : [];
        $outEventListMap = [];
        foreach (array_keys($defaults) as $k) {
            if (array_key_exists($k, $eventListMap)) {
                $raw = $eventListMap[$k];
                if ($raw === '' || $raw === null) {
                    continue;
                }
                $outEventListMap[$k] = (int) $raw;
            }
        }
        $out['event_list_map'] = $outEventListMap;

        $siteEventListMap = isset($value['site_event_list_map']) && is_array($value['site_event_list_map']) ? $value['site_event_list_map'] : [];
        $outSiteMap = [];
        foreach ($siteEventListMap as $siteUrl => $map) {
            $siteUrl = esc_url_raw(trim((string) $siteUrl));
            if ($siteUrl === '' || !is_array($map)) {
                continue;
            }
            $clean = [];
            foreach (array_keys($defaults) as $k) {
                if (array_key_exists($k, $map)) {
                    $raw = $map[$k];
                    if ($raw === '' || $raw === null) {
                        continue;
                    }
                    $clean[$k] = (int) $raw;
                }
            }
            if (!empty($clean)) {
                $outSiteMap[$siteUrl] = $clean;
            }
        }
        $out['site_event_list_map'] = $outSiteMap;

        return $out;
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = get_option(MAILZEN_INTEGRATION_OPTION_KEY, []);
        $settings = is_array($settings) ? $settings : [];

        $client = MailZenClient::fromSettings($settings);

        $lists = [];
        if ($client) {
            $cacheKey = 'mailzen_lists_' . md5(($settings['base_url'] ?? '') . '|' . ($settings['api_key'] ?? ''));
            $lists = get_transient($cacheKey);
            if (!is_array($lists)) {
                $resp = $client->fetchLists();
                $lists = [];
                if (($resp['ok'] ?? false) && is_array($resp['data']['data'] ?? null)) {
                    $lists = $resp['data']['data'];
                }
                set_transient($cacheKey, $lists, 10 * MINUTE_IN_SECONDS);
            }
        }

        $defaults = json_decode(MAILZEN_INTEGRATION_DEFAULT_EVENTS, true);
        $defaults = is_array($defaults) ? $defaults : [];

        $events = isset($settings['events']) && is_array($settings['events']) ? $settings['events'] : [];
        $eventListMap = isset($settings['event_list_map']) && is_array($settings['event_list_map']) ? $settings['event_list_map'] : [];
        $siteEventListMap = isset($settings['site_event_list_map']) && is_array($settings['site_event_list_map']) ? $settings['site_event_list_map'] : [];

        $currentSite = get_site_url();
        if (!isset($siteEventListMap[$currentSite]) || !is_array($siteEventListMap[$currentSite])) {
            $siteEventListMap[$currentSite] = [];
        }

        $testUrl = admin_url('admin-post.php');

        ?>
        <div class="wrap">
            <h1>MailZen Integration</h1>

            <?php if (isset($_GET['mailzen_test']) && $_GET['mailzen_test'] === '1'): ?>
                <div class="notice notice-success"><p>Connection successful.</p></div>
            <?php elseif (isset($_GET['mailzen_test']) && $_GET['mailzen_test'] === '0'): ?>
                <div class="notice notice-error"><p>Connection failed. Check Base URL and API Key.</p></div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields('mailzen_integration'); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="mailzen_base_url">Base URL</label></th>
                            <td>
                                <input id="mailzen_base_url" type="url" class="regular-text" name="<?php echo esc_attr(MAILZEN_INTEGRATION_OPTION_KEY); ?>[base_url]" value="<?php echo esc_attr($settings['base_url'] ?? ''); ?>" placeholder="https://your-mailzen-domain.com" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="mailzen_api_key">API Key</label></th>
                            <td>
                                <input id="mailzen_api_key" type="password" class="regular-text" name="<?php echo esc_attr(MAILZEN_INTEGRATION_OPTION_KEY); ?>[api_key]" value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>" autocomplete="off" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Signing</th>
                            <td>
                                <input type="hidden" name="<?php echo esc_attr(MAILZEN_INTEGRATION_OPTION_KEY); ?>[signing_secret]" value="<?php echo esc_attr($settings['signing_secret'] ?? ''); ?>" />
                                <?php if (!empty($settings['signing_secret'])): ?>
                                    <span>Enabled (secret synced)</span>
                                <?php else: ?>
                                    <span>Not synced yet</span>
                                <?php endif; ?>
                                <p class="description">Signing secret is fetched from MailZen when you click Test Connection.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="mailzen_list_id">Default List</label></th>
                            <td>
                                <select id="mailzen_list_id" name="<?php echo esc_attr(MAILZEN_INTEGRATION_OPTION_KEY); ?>[list_id]">
                                    <option value="0">No list (use MailZen system list)</option>
                                    <?php foreach ($lists as $l):
                                        $id = (int) ($l['id'] ?? 0);
                                        $name = (string) ($l['name'] ?? '');
                                        if ($id <= 0) continue;
                                    ?>
                                        <option value="<?php echo esc_attr($id); ?>" <?php selected((int) ($settings['list_id'] ?? 0), $id); ?>><?php echo esc_html($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!$client): ?>
                                    <p class="description">Enter Base URL + API Key and save to load lists.</p>
                                <?php endif; ?>
                                <p class="description">Used as fallback if no per-event/per-site mapping is set.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Events</th>
                            <td>
                                <?php foreach (array_keys($defaults) as $eventKey): ?>
                                    <label style="display:block; margin-bottom:6px;">
                                        <input type="checkbox" name="<?php echo esc_attr(MAILZEN_INTEGRATION_OPTION_KEY); ?>[events][<?php echo esc_attr($eventKey); ?>]" value="1" <?php checked(!empty($events[$eventKey])); ?> />
                                        <?php echo esc_html($eventKey); ?>
                                    </label>
                                <?php endforeach; ?>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Event → List mapping</th>
                            <td>
                                <p class="description">Optional. Use this to send specific events to different lists. Set to "No list" to use MailZen system list.</p>
                                <table class="widefat striped" style="max-width: 760px;">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>List</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_keys($defaults) as $eventKey): ?>
                                            <tr>
                                                <td><?php echo esc_html($eventKey); ?></td>
                                                <td>
                                                    <select name="<?php echo esc_attr(MAILZEN_INTEGRATION_OPTION_KEY); ?>[event_list_map][<?php echo esc_attr($eventKey); ?>]">
                                                        <option value="">(inherit)</option>
                                                        <option value="0" <?php selected((string)($eventListMap[$eventKey] ?? ''), '0'); ?>>No list (system)</option>
                                                        <?php foreach ($lists as $l):
                                                            $id = (int) ($l['id'] ?? 0);
                                                            $name = (string) ($l['name'] ?? '');
                                                            if ($id <= 0) continue;
                                                        ?>
                                                            <option value="<?php echo esc_attr($id); ?>" <?php selected((int)($eventListMap[$eventKey] ?? -1), $id); ?>><?php echo esc_html($name); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Per-site → Event → List mapping</th>
                            <td>
                                <p class="description">Optional. If you use WordPress multisite or manage multiple site URLs, you can override list mapping per site.</p>
                                <?php foreach ($siteEventListMap as $siteUrl => $map):
                                    if (!is_array($map)) continue;
                                ?>
                                    <div style="margin: 12px 0; padding: 12px; border: 1px solid #ccd0d4; background: #fff; max-width: 760px;">
                                        <div style="margin-bottom: 10px;"><strong>Site:</strong> <?php echo esc_html($siteUrl); ?></div>
                                        <input type="hidden" name="<?php echo esc_attr(MAILZEN_INTEGRATION_OPTION_KEY); ?>[site_event_list_map][<?php echo esc_attr($siteUrl); ?>][__enabled]" value="1" />
                                        <table class="widefat striped">
                                            <thead>
                                                <tr>
                                                    <th>Event</th>
                                                    <th>List</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_keys($defaults) as $eventKey): ?>
                                                    <tr>
                                                        <td><?php echo esc_html($eventKey); ?></td>
                                                        <td>
                                                            <select name="<?php echo esc_attr(MAILZEN_INTEGRATION_OPTION_KEY); ?>[site_event_list_map][<?php echo esc_attr($siteUrl); ?>][<?php echo esc_attr($eventKey); ?>]">
                                                                <option value="">(inherit)</option>
                                                                <option value="0" <?php selected((string)($map[$eventKey] ?? ''), '0'); ?>>No list (system)</option>
                                                                <?php foreach ($lists as $l):
                                                                    $id = (int) ($l['id'] ?? 0);
                                                                    $name = (string) ($l['name'] ?? '');
                                                                    if ($id <= 0) continue;
                                                                ?>
                                                                    <option value="<?php echo esc_attr($id); ?>" <?php selected((int)($map[$eventKey] ?? -1), $id); ?>><?php echo esc_html($name); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button('Save Settings'); ?>
            </form>

            <form method="post" action="<?php echo esc_url($testUrl); ?>">
                <?php wp_nonce_field('mailzen_integration_test'); ?>
                <input type="hidden" name="action" value="mailzen_integration_test" />
                <?php submit_button('Test Connection', 'secondary'); ?>
            </form>
        </div>
        <?php
    }

    public static function handleTestConnection(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('mailzen_integration_test');

        $settings = get_option(MAILZEN_INTEGRATION_OPTION_KEY, []);
        $settings = is_array($settings) ? $settings : [];

        $client = MailZenClient::fromSettings($settings);
        $ok = false;

        $newSecret = '';

        if ($client) {
            $resp = $client->fetchLists();
            $ok = (bool) ($resp['ok'] ?? false);

            if ($ok) {
                $sigResp = $client->fetchSigningSecret();
                if (($sigResp['ok'] ?? false) && is_array($sigResp['data']['data'] ?? null)) {
                    $newSecret = trim((string) ($sigResp['data']['data']['signing_secret'] ?? ''));
                }
            }
        }

        if ($ok && $newSecret !== '') {
            $settings['signing_secret'] = $newSecret;
            update_option(MAILZEN_INTEGRATION_OPTION_KEY, $settings, false);
        }

        wp_safe_redirect(add_query_arg([
            'page' => 'mailzen-integration',
            'mailzen_test' => $ok ? '1' : '0',
        ], admin_url('options-general.php')));
        exit;
    }
}
