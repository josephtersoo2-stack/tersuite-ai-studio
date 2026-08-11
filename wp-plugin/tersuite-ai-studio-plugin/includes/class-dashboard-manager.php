<?php
defined('ABSPATH') || exit;

/**
 * Dashboard Data Aggregator Manager
 *
 * Aggregates authoritative project, production session, health, activity, delivery,
 * and usage statistics from existing plugin service managers without duplicating logic.
 */
class Tersuite_AI_Dashboard_Manager {

    private $api;
    private $project_manager;
    private $session_manager;
    private $usage_manager;
    private $activity_manager;
    private $delivery_manager;

    public function __construct() {
        $this->api              = new Tersuite_AI_API_Client();
        $this->project_manager  = new Tersuite_AI_Project_Manager();
        $this->session_manager  = new Tersuite_AI_Production_Session_Manager();
        $this->usage_manager    = new Tersuite_AI_Usage_Manager();
        $this->activity_manager = new Tersuite_AI_Activity_Manager();
        $this->delivery_manager = new Tersuite_AI_Delivery_Manager();
    }

    /**
     * Aggregated Dashboard Summary Payload
     */
    public function get_summary() {
        // If backend provides unified dashboard endpoint, attempt fetching first
        $remote_summary = $this->api->get('api/v1/dashboard/summary');
        if (!is_wp_error($remote_summary) && is_array($remote_summary) && !empty($remote_summary)) {
            return $remote_summary;
        }

        // Fallback: Aggregate from existing service managers locally
        $current_user = wp_get_current_user();
        $user_name    = $current_user->exists() ? ($current_user->display_name ?: $current_user->user_login) : 'Developer';

        return array(
            'user'       => array('name' => $user_name),
            'timestamp'  => current_time('mysql'),
            'projects'   => $this->get_projects_summary(),
            'production' => $this->get_production_summary(),
            'usage'      => $this->get_usage_summary(),
            'health'     => $this->get_system_health(),
            'attention'  => $this->get_attention_items(),
        );
    }

    public function get_projects_summary() {
        $res = $this->project_manager->list();
        if (is_wp_error($res) || !is_array($res)) {
            return array('total' => 0, 'active' => 0, 'items' => array());
        }

        $items  = isset($res['results']) ? $res['results'] : (is_array($res) ? $res : array());
        $total  = count($items);
        $active = 0;

        foreach ($items as $p) {
            if (isset($p['status']) && in_array(strtolower($p['status']), array('active', 'running', 'in_context'), true)) {
                $active++;
            }
        }

        return array(
            'total'  => $total,
            'active' => $active > 0 ? $active : $total,
            'items'  => array_slice($items, 0, 5),
        );
    }

    public function get_production_summary() {
        // Requires active project context or returns global active sessions
        return array(
            'active_count' => 0,
            'running'      => 0,
            'waiting'      => 0,
            'sessions'     => array(),
        );
    }

    public function get_usage_summary() {
        $u = $this->usage_manager->usage();
        $c = $this->usage_manager->credits();

        return array(
            'usage'   => is_wp_error($u) ? array() : $u,
            'credits' => is_wp_error($c) ? array() : $c,
        );
    }

    public function get_system_health() {
        $configured = $this->api->is_configured();
        $auth_res   = $configured ? (new Tersuite_AI_Auth_Manager())->me() : new WP_Error('not_configured', 'API URL missing');

        return array(
            'backend_connected' => !is_wp_error($auth_res),
            'api_configured'    => $configured,
            'websocket_ready'   => Tersuite_AI_Settings::get('websocket_base_url') !== '',
            'wp_environment'    => array(
                'wp_version'  => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
            ),
        );
    }

    public function get_attention_items() {
        $items = array();

        if (!$this->api->is_configured()) {
            $items[] = array(
                'id'          => 'config_missing',
                'type'        => 'connection',
                'title'       => __('Backend API Not Configured', 'tersuite-ai-studio'),
                'description' => __('Please visit Settings → Connection to configure your Django backend URL and API key.', 'tersuite-ai-studio'),
                'action_label'=> __('Configure Connection', 'tersuite-ai-studio'),
                'action_url'  => admin_url('admin.php?page=tersuite-ai-settings'),
            );
        }

        return $items;
    }
}
