<?php
/**
 * Plugin Name: {{PLUGIN_NAME}}
 * Plugin URI: {{PLUGIN_URI}}
 * Description: {{PLUGIN_DESCRIPTION}}
 * Version: {{VERSION}}
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: {{AUTHOR}}
 * Author URI: {{AUTHOR_URI}}
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: {{TEXT_DOMAIN}}
 * Domain Path: /languages
 */

// Security: Prevent direct file access (Zero-Trust by default)
if (!defined('ABSPATH')) {
    exit;
}

namespace {{PLUGIN_NAMESPACE}};

use {{PLUGIN_NAMESPACE}}\Admin\SettingsPage;
use {{PLUGIN_NAMESPACE}}\Security\NonceManager;

/**
 * Main plugin class following modern PHP standards.
 */
final class Plugin {
    private static $instance = null;

    public static function instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks(): void {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('admin_menu', [$this, 'register_admin_pages']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_{{ACTION_PREFIX}}_action', [$this, 'handle_ajax']);
    }

    public function load_textdomain(): void {
        load_plugin_textdomain('{{TEXT_DOMAIN}}', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function register_admin_pages(): void {
        add_menu_page(
            __('Plugin Settings', '{{TEXT_DOMAIN}}'),
            __('Plugin Name', '{{TEXT_DOMAIN}}'),
            'manage_options',
            '{{SLUG}}',
            [new SettingsPage(), 'render'],
            'dashicons-admin-generic',
            30
        );
    }

    public function register_settings(): void {
        register_setting('{{OPTION_GROUP}}', '{{OPTION_NAME}}', [
            'sanitize_callback' => [Security\Sanitizer::class, 'sanitize_options'],
        ]);
    }

    public function handle_ajax(): void {
        // Nonce verification (security required)
        if (!wp_verify_nonce($_REQUEST['nonce'] ?? '', '{{ACTION_PREFIX}}_nonce')) {
            wp_send_json_error(__('Security check failed.', '{{TEXT_DOMAIN}}'), 403);
        }

        // Capability check (RBAC)
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', '{{TEXT_DOMAIN}}'), 403);
        }

        // Process AJAX request with sanitized input
        $input = Security\Sanitizer::sanitize_text($_POST['data'] ?? '');
        wp_send_json_success(['message' => 'Processed securely.', 'data' => $input]);
    }
}

// Initialize plugin
Plugin::instance();
