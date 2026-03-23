<?php

namespace MailZenIntegration;

class MailZenHooks
{
    public static function register(): void
    {
        add_action('user_register', [self::class, 'onUserRegister'], 10, 1);
        add_action('profile_update', [self::class, 'onUserUpdate'], 10, 2);

        if (class_exists('WooCommerce')) {
            add_action('woocommerce_created_customer', [self::class, 'onWooCustomerCreated'], 10, 3);
            add_action('woocommerce_checkout_order_processed', [self::class, 'onWooOrderCreated'], 10, 3);
            add_action('woocommerce_payment_complete', [self::class, 'onWooOrderPaid'], 10, 1);
            add_action('woocommerce_order_status_completed', [self::class, 'onWooOrderCompleted'], 10, 2);
            add_action('woocommerce_order_refunded', [self::class, 'onWooOrderRefunded'], 10, 2);
            add_action('woocommerce_order_status_cancelled', [self::class, 'onWooOrderCancelled'], 10, 2);
        }
    }

    private static function settings(): array
    {
        $settings = get_option(MAILZEN_INTEGRATION_OPTION_KEY, []);
        return is_array($settings) ? $settings : [];
    }

    private static function isEnabled(string $event): bool
    {
        $settings = self::settings();
        $events = isset($settings['events']) && is_array($settings['events']) ? $settings['events'] : [];
        return !empty($events[$event]);
    }

    private static function listId(): int
    {
        $settings = self::settings();
        return (int) ($settings['list_id'] ?? 0);
    }

    private static function resolveListId(string $event): int
    {
        $settings = self::settings();

        $siteUrl = strtolower(trim((string) get_site_url()));
        $siteMaps = isset($settings['site_event_list_map']) && is_array($settings['site_event_list_map'])
            ? $settings['site_event_list_map']
            : [];

        if ($siteUrl !== '') {
            foreach ($siteMaps as $siteKey => $eventMap) {
                $siteKeyNorm = strtolower(trim((string) $siteKey));
                if ($siteKeyNorm === '' || $siteKeyNorm !== $siteUrl) {
                    continue;
                }
                if (is_array($eventMap) && array_key_exists($event, $eventMap)) {
                    return (int) $eventMap[$event];
                }
            }
        }

        $eventMap = isset($settings['event_list_map']) && is_array($settings['event_list_map'])
            ? $settings['event_list_map']
            : [];

        if (array_key_exists($event, $eventMap)) {
            return (int) $eventMap[$event];
        }

        return (int) ($settings['list_id'] ?? 0);
    }

    private static function baseEventEnvelope(string $event, string $email, array $extra = []): array
    {
        $siteUrl = get_site_url();
        $listId = self::resolveListId($event);

        $base = [
            'event' => $event,
            'occurred_at' => gmdate('c'),
            'email' => $email,
            'site' => [
                'url' => $siteUrl,
                'name' => get_bloginfo('name'),
            ],
        ];

        if ($listId > 0) {
            $base['list_id'] = $listId;
        }

        return array_merge($base, $extra);
    }

    public static function onUserRegister(int $userId): void
    {
        if (!self::isEnabled('wp_user_registered')) {
            return;
        }

        $user = get_userdata($userId);
        if (!$user) {
            return;
        }

        $email = strtolower(trim((string) $user->user_email));
        if (!is_email($email)) {
            return;
        }

        $payload = self::baseEventEnvelope('wp_user_registered', $email, [
            'external_id' => 'wp_user_' . (int) $userId . '_registered',
            'first_name' => get_user_meta($userId, 'first_name', true) ?: '',
            'last_name' => get_user_meta($userId, 'last_name', true) ?: '',
            'tags' => ['wordpress', 'user_registered'],
            'custom_fields' => [
                'wp_user_id' => (int) $userId,
            ],
            'payload' => [
                'user_id' => (int) $userId,
                'roles' => is_array($user->roles) ? array_values($user->roles) : [],
            ],
        ]);

        MailZenQueue::enqueue($payload);
    }

    public static function onUserUpdate(int $userId, $oldUserData): void
    {
        if (!self::isEnabled('wp_user_updated')) {
            return;
        }

        $user = get_userdata($userId);
        if (!$user) {
            return;
        }

        $email = strtolower(trim((string) $user->user_email));
        if (!is_email($email)) {
            return;
        }

        $payload = self::baseEventEnvelope('wp_user_updated', $email, [
            'external_id' => 'wp_user_' . (int) $userId . '_updated_' . time(),
            'first_name' => get_user_meta($userId, 'first_name', true) ?: '',
            'last_name' => get_user_meta($userId, 'last_name', true) ?: '',
            'tags' => ['wordpress', 'user_updated'],
            'custom_fields' => [
                'wp_user_id' => (int) $userId,
            ],
            'payload' => [
                'user_id' => (int) $userId,
            ],
        ]);

        MailZenQueue::enqueue($payload);
    }

    public static function onWooOrderCreated($orderId, $postedData, $order): void
    {
        if (!self::isEnabled('woo_order_created')) {
            return;
        }

        $wcOrder = is_object($order) ? $order : (function_exists('wc_get_order') ? wc_get_order($orderId) : null);
        if (!$wcOrder) {
            return;
        }

        $email = strtolower(trim((string) $wcOrder->get_billing_email()));
        if (!is_email($email)) {
            return;
        }

        $eventPayload = self::wooPayload('woo_order_created', $wcOrder, [
            'tags' => ['woocommerce', 'order_created'],
        ]);

        MailZenQueue::enqueue($eventPayload);

        if (self::isEnabled('woo_abandoned_checkout')) {
            $abandoned = self::wooPayload('woo_abandoned_checkout', $wcOrder, [
                'tags' => ['woocommerce', 'abandoned_checkout'],
            ]);

            MailZenQueue::enqueueDelayed($abandoned, 60 * 60);
        }
    }

    public static function onWooCustomerCreated($customerId, $newCustomerData, $passwordGenerated): void
    {
        if (!self::isEnabled('woo_customer_created')) {
            return;
        }

        $user = get_userdata((int) $customerId);
        if (!$user) {
            return;
        }

        $email = strtolower(trim((string) $user->user_email));
        if (!is_email($email)) {
            return;
        }

        $payload = self::baseEventEnvelope('woo_customer_created', $email, [
            'external_id' => 'woo_customer_' . (int) $customerId . '_created',
            'first_name' => get_user_meta((int) $customerId, 'first_name', true) ?: '',
            'last_name' => get_user_meta((int) $customerId, 'last_name', true) ?: '',
            'tags' => ['woocommerce', 'customer_created'],
            'custom_fields' => [
                'wp_user_id' => (int) $customerId,
                'woo_customer_id' => (int) $customerId,
            ],
            'payload' => [
                'woo_customer_id' => (int) $customerId,
                'roles' => is_array($user->roles) ? array_values($user->roles) : [],
            ],
        ]);

        MailZenQueue::enqueue($payload);
    }

    public static function onWooOrderPaid($orderId): void
    {
        if (!self::isEnabled('woo_order_paid')) {
            return;
        }

        $wcOrder = function_exists('wc_get_order') ? wc_get_order($orderId) : null;
        if (!$wcOrder) {
            return;
        }

        $email = strtolower(trim((string) $wcOrder->get_billing_email()));
        if (!is_email($email)) {
            return;
        }

        MailZenQueue::enqueue(self::wooPayload('woo_order_paid', $wcOrder, [
            'tags' => ['woocommerce', 'order_paid'],
        ]));
    }

    public static function onWooOrderCompleted($orderId, $order): void
    {
        if (!self::isEnabled('woo_order_completed')) {
            return;
        }

        $wcOrder = is_object($order) ? $order : (function_exists('wc_get_order') ? wc_get_order($orderId) : null);
        if (!$wcOrder) {
            return;
        }

        $email = strtolower(trim((string) $wcOrder->get_billing_email()));
        if (!is_email($email)) {
            return;
        }

        MailZenQueue::enqueue(self::wooPayload('woo_order_completed', $wcOrder, [
            'tags' => ['woocommerce', 'order_completed'],
        ]));
    }

    public static function onWooOrderRefunded($orderId, $refundId): void
    {
        if (!self::isEnabled('woo_order_refunded')) {
            return;
        }

        $wcOrder = function_exists('wc_get_order') ? wc_get_order($orderId) : null;
        if (!$wcOrder) {
            return;
        }

        $email = strtolower(trim((string) $wcOrder->get_billing_email()));
        if (!is_email($email)) {
            return;
        }

        MailZenQueue::enqueue(self::wooPayload('woo_order_refunded', $wcOrder, [
            'external_id' => 'wc_order_' . (int) $orderId . '_refund_' . (int) $refundId,
            'tags' => ['woocommerce', 'order_refunded'],
            'payload' => array_merge(self::wooOrderPayload($wcOrder), [
                'refund_id' => (int) $refundId,
            ]),
        ]));
    }

    public static function onWooOrderCancelled($orderId, $order): void
    {
        if (!self::isEnabled('woo_order_cancelled')) {
            return;
        }

        $wcOrder = is_object($order) ? $order : (function_exists('wc_get_order') ? wc_get_order($orderId) : null);
        if (!$wcOrder) {
            return;
        }

        $email = strtolower(trim((string) $wcOrder->get_billing_email()));
        if (!is_email($email)) {
            return;
        }

        MailZenQueue::enqueue(self::wooPayload('woo_order_cancelled', $wcOrder, [
            'tags' => ['woocommerce', 'order_cancelled'],
        ]));
    }

    private static function wooPayload(string $event, $order, array $override = []): array
    {
        $email = strtolower(trim((string) $order->get_billing_email()));
        $userId = (int) $order->get_user_id();

        $base = self::baseEventEnvelope($event, $email, [
            'external_id' => 'wc_order_' . (int) $order->get_id() . '_' . $event,
            'first_name' => (string) $order->get_billing_first_name(),
            'last_name' => (string) $order->get_billing_last_name(),
            'custom_fields' => [
                'wp_user_id' => $userId > 0 ? $userId : null,
                'woo_customer_id' => $userId > 0 ? $userId : null,
            ],
            'payload' => self::wooOrderPayload($order),
        ]);

        if (!isset($override['payload'])) {
            $override['payload'] = $base['payload'];
        }

        return array_merge($base, $override);
    }

    private static function wooOrderPayload($order): array
    {
        $items = [];

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();

            $productId = $product ? (int) $product->get_id() : (int) $item->get_product_id();
            $categories = [];
            if ($productId > 0 && function_exists('wp_get_post_terms')) {
                $terms = wp_get_post_terms($productId, 'product_cat', ['fields' => 'slugs']);
                if (is_array($terms)) {
                    $categories = array_values(array_filter(array_map('strval', $terms)));
                }
            }

            $items[] = [
                'product_id' => $productId,
                'sku' => $product ? (string) $product->get_sku() : '',
                'name' => (string) $item->get_name(),
                'qty' => (int) $item->get_quantity(),
                'total' => (float) $item->get_total(),
                'categories' => $categories,
            ];
        }

        return [
            'order_id' => (int) $order->get_id(),
            'status' => (string) $order->get_status(),
            'total' => (float) $order->get_total(),
            'currency' => (string) $order->get_currency(),
            'payment_method' => (string) $order->get_payment_method(),
            'items' => $items,
        ];
    }
}
