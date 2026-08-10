<?php
/**
 * Plugin Name: Tersuite AI Studio Plugin Generator
 * Plugin URI: https://tersuite.example.com
 * Description: Create and manage WordPress plugins directly inside the admin dashboard. Connects to Tersuite AI Studio AI backend for multi-agent plugin generation with real-time streaming.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Tersuite AI Studio
 * Author URI: https://tersuite.example.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tersuite
 */

if (!defined('ABSPATH')) {
    exit;
}

final class PluginGenerator {
    private static $instance = null;

    public static function instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        $this->init_hooks();
        $this->load_dependencies();
    }

    private function define_constants(): void {
        define('TERSUITE_VERSION', '1.0.0');
        define('TERSUITE_PATH', plugin_dir_path(__FILE__));
        define('TERSUITE_URL', plugin_dir_url(__FILE__));
        define('TERSUITE_API_ENDPOINT', admin_url('admin-ajax.php'));
    }

    private function load_dependencies(): void {
        require_once TERSUITE_PATH . 'includes/class-api-client.php';
        require_once TERSUITE_PATH . 'includes/class-project-manager.php';
        require_once TERSUITE_PATH . 'includes/class-file-manager.php';
        require_once TERSUITE_PATH . 'includes/class-ide-page.php';
    }

    private function init_hooks(): void {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_tersuite_create_project', [$this, 'handle_create_project']);
        add_action('wp_ajax_tersuite_start_agents', [$this, 'handle_start_agents']);
        add_action('wp_ajax_tersuite_fetch_progress', [$this, 'handle_fetch_progress']);
        add_action('wp_ajax_tersuite_download_plugin', [$this, 'handle_download_plugin']);
        add_action('wp_ajax_tersuite_fetch_subscription', [$this, 'handle_fetch_subscription']);
        add_action('admin_init', [$this, 'register_plugin_settings']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_action_links']);
    }

    public function register_admin_menu(): void {
        add_menu_page(
            __('Tersuite AI Studio', 'tersuite'),
            __('Tersuite AI Studio', 'tersuite'),
            'manage_options',
            'tersuite',
            [$this, 'render_dashboard'],
            'dashicons-admin-tools',
            30
        );

        add_submenu_page(
            'tersuite',
            __('Dashboard', 'tersuite'),
            __('Dashboard', 'tersuite'),
            'manage_options',
            'tersuite',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'tersuite',
            __('Create Plugin', 'tersuite'),
            __('Create Plugin', 'tersuite'),
            'manage_options',
            'tersuite-create',
            [$this, 'render_create_page']
        );

        // Settings page
        add_submenu_page(
            'tersuite',
            __('Settings', 'tersuite'),
            __('Settings', 'tersuite'),
            'manage_options',
            'tersuite-settings',
            [$this, 'render_settings']
        );
    }

    public function render_dashboard(): void {
        include TERSUITE_PATH . 'templates/dashboard.php';
    }

    public function register_plugin_settings(): void {
        register_setting('tersuite_options', 'tersuite_api_base', 'sanitize_url');
        register_setting('tersuite_options', 'tersuite_api_key', 'sanitize_text_field');
        register_setting('tersuite_options', 'tersuite_stream_url', 'sanitize_url');
    }

    public function render_settings(): void {
        include TERSUITE_PATH . 'templates/settings.php';
    }

    public function render_create_page(): void {
        include TERSUITE_PATH . 'templates/create-project.php';
    }

    public function enqueue_admin_assets(string $hook): void {
        if (strpos($hook, 'tersuite') === false) {
            return;
        }
        wp_enqueue_style('tersuite-admin', TERSUITE_URL . 'assets/css/admin.css', [], TERSUITE_VERSION);
        wp_enqueue_script('tersuite-admin', TERSUITE_URL . 'assets/js/admin.js', ['jquery'], TERSUITE_VERSION, true);
        wp_localize_script('tersuite-admin', 'tersuiteData', [
            'apiUrl' => TERSUITE_API_ENDPOINT,
            'nonce' => wp_create_nonce('tersuite_nonce'),
            'streamUrl' => get_option('tersuite_stream_url', ''),
            'apiKey' => get_option('tersuite_api_key', ''),
        ]);
    }

    public function handle_create_project(): void {
        check_ajax_referer('tersuite_nonce', 'nonce');
        $api_client = new APIClient();
        $project_name = sanitize_text_field($_POST['project_name'] ?? '');
        if (empty($project_name)) {
            wp_send_json_error(['message' => 'Project name is required.']);
        }
        $description = sanitize_textarea_field($_POST['plugin_description'] ?? '');
        $response = $api_client->post('projects/', ['name' => $project_name, 'description' => $description]);
        wp_send_json_success($response);
    }

    public function handle_start_agents(): void {
        check_ajax_referer('tersuite_nonce', 'nonce');
        $project_id = sanitize_text_field($_POST['project_id'] ?? '');
        $task = sanitize_textarea_field($_POST['task'] ?? 'Generate a new WordPress plugin');
        $api_client = new APIClient();
        $response = $api_client->post("projects/{$project_id}/start/", ['task' => $task]);
        wp_send_json_success($response);
    }

    public function handle_fetch_progress(): void {
        check_ajax_referer('tersuite_nonce', 'nonce');
        $project_id = sanitize_text_field($_POST['project_id'] ?? '');
        $api_client = new APIClient();
        $response = $api_client->get("projects/{$project_id}/stream/");
        wp_send_json_success($response);
    }

    public function handle_download_plugin(): void {
        check_ajax_referer('tersuite_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to download generated plugins.', 'tersuite'), 403);
        }
        $project_id = sanitize_text_field($_POST['project_id'] ?? '');
        if (!$project_id || !preg_match('/^[a-f0-9-]{36}$/i', $project_id)) {
            wp_die(esc_html__('Invalid project ID.', 'tersuite'), 400);
        }

        $api_client = new APIClient();
        $response = $api_client->get("projects/{$project_id}/deliver/");
        if (($response['status'] ?? '') !== 'ready' || empty($response['files']) || !is_array($response['files'])) {
            wp_die(esc_html($response['message'] ?? $response['error'] ?? 'Plugin is not ready.'), 409);
        }
        if (!class_exists('ZipArchive')) {
            wp_die(esc_html__('PHP ZipArchive is required on the WordPress server.', 'tersuite'), 500);
        }

        $upload = wp_upload_dir();
        $tmp_dir = trailingslashit($upload['basedir']) . 'tersuite-tmp';
        if (!wp_mkdir_p($tmp_dir)) {
            wp_die(esc_html__('Unable to create temporary package directory.', 'tersuite'), 500);
        }
        $zip_path = trailingslashit($tmp_dir) . 'tersuite-' . sanitize_file_name($project_id) . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            wp_die(esc_html__('Unable to create plugin ZIP.', 'tersuite'), 500);
        }

        foreach ($response['files'] as $relative_path => $contents) {
            $relative_path = str_replace('\\', '/', (string) $relative_path);
            $relative_path = ltrim($relative_path, '/');
            if ($relative_path === '' || $relative_path === '.' || str_contains($relative_path, '../') || str_contains($relative_path, '/..') || preg_match('#^\.\.?/#', $relative_path)) {
                $zip->close();
                @unlink($zip_path);
                wp_die(esc_html__('Backend returned an unsafe file path.', 'tersuite'), 500);
            }
            $zip->addFromString($relative_path, (string) $contents);
        }
        $zip->close();

        nocache_headers();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="tersuite-plugin-' . sanitize_file_name($project_id) . '.zip"');
        header('Content-Length: ' . filesize($zip_path));
        readfile($zip_path);
        @unlink($zip_path);
        exit;
    }

    public function handle_fetch_subscription(): void {
        check_ajax_referer('tersuite_nonce', 'nonce');
        $api_client = new APIClient();
        $response = $api_client->get('subscriptions/status/');
        wp_send_json_success($response);
    }

    public function add_action_links(array $links): array {
        $links[] = '<a href="' . admin_url('admin.php?page=tersuite') . '">' . __('Dashboard', 'tersuite') . '</a>';
        return $links;
    }
}

// Initialize plugin
PluginGenerator::instance();
