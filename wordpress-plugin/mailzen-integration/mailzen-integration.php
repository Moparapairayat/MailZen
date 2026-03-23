<?php
/*
Plugin Name: MailZen Integration
Description: Send WordPress and WooCommerce events to MailZen automations.
Version: 0.1.0
Author: MOPARA PAIR AYAT & Fatema Binte Mariam
*/

if (!defined('ABSPATH')) {
    exit;
}

define('MAILZEN_INTEGRATION_VERSION', '0.1.0');
define('MAILZEN_INTEGRATION_PLUGIN_FILE', __FILE__);
define('MAILZEN_INTEGRATION_PLUGIN_DIR', plugin_dir_path(__FILE__));

define('MAILZEN_INTEGRATION_OPTION_KEY', 'mailzen_integration_settings');

define('MAILZEN_INTEGRATION_QUEUE_TABLE', 'mailzen_event_queue');

define('MAILZEN_INTEGRATION_CRON_HOOK', 'mailzen_integration_process_queue');

define('MAILZEN_INTEGRATION_DEFAULT_EVENTS', wp_json_encode([
    'wp_user_registered' => true,
    'wp_user_updated' => false,
    'woo_customer_created' => true,
    'woo_order_created' => true,
    'woo_order_paid' => true,
    'woo_order_completed' => true,
    'woo_order_refunded' => true,
    'woo_order_cancelled' => true,
    'woo_abandoned_checkout' => false,
]));

require_once MAILZEN_INTEGRATION_PLUGIN_DIR . 'src/MailZenClient.php';
require_once MAILZEN_INTEGRATION_PLUGIN_DIR . 'src/MailZenQueue.php';
require_once MAILZEN_INTEGRATION_PLUGIN_DIR . 'src/MailZenSettingsPage.php';
require_once MAILZEN_INTEGRATION_PLUGIN_DIR . 'src/MailZenHooks.php';

register_activation_hook(MAILZEN_INTEGRATION_PLUGIN_FILE, function () {
    \MailZenIntegration\MailZenQueue::activate();
});

register_deactivation_hook(MAILZEN_INTEGRATION_PLUGIN_FILE, function () {
    \MailZenIntegration\MailZenQueue::deactivate();
});

add_action('plugins_loaded', function () {
    \MailZenIntegration\MailZenSettingsPage::register();
    \MailZenIntegration\MailZenQueue::register();
    \MailZenIntegration\MailZenHooks::register();
});
