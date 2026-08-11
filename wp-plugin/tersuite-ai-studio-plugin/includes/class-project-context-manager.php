<?php
defined('ABSPATH') || exit;

/**
 * Project Context Manager
 *
 * Assembles current UI environment state (screen, selected file, version, active session)
 * and requests full authoritative project context from Django.
 */
class Tersuite_AI_Project_Context_Manager {

    private $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    /**
     * Build UI context payload.
     *
     * @param string|int $project_id
     * @param string     $screen
     * @param string     $selected_file
     * @param string     $version_id
     * @param string     $session_id
     * @return array
     */
    public function ui_payload($project_id, $screen = 'ai_studio', $selected_file = '', $version_id = '', $session_id = '') {
        $route = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : 'tersuite-ai-studio';

        return array(
            'project_id'       => (string) $project_id,
            'screen'           => sanitize_key($screen),
            'selected_file'    => sanitize_text_field($selected_file),
            'selected_version' => sanitize_text_field($version_id),
            'active_session'   => sanitize_text_field($session_id),
            'route'            => $route,
            'wp_environment'   => array(
                'wp_version'  => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
                'site_url'    => site_url(),
                'is_multisite' => is_multisite(),
            ),
        );
    }

    /**
     * Request authoritative project context from Django.
     *
     * @param string|int $project_id
     * @param array      $ui_context
     * @return array|WP_Error
     */
    public function get_context($project_id, $ui_context = array()) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to load context.', 'tersuite-ai-studio'));
        }

        $payload = array(
            'ui_context' => !empty($ui_context) ? $ui_context : $this->ui_payload($project_id),
        );

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/coordinator/context';
        return $this->api->post($path, $payload);
    }
}
