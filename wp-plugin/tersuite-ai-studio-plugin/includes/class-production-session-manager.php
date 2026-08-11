<?php
defined('ABSPATH') || exit;

/**
 * Production Session Manager
 *
 * Exposes production session inspection and cancellation to the UI.
 * Django is authoritative for session execution, queueing, and task graphs.
 */
class Tersuite_AI_Production_Session_Manager {

    private $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    /**
     * List production sessions for a project.
     *
     * @param string|int $project_id
     * @return array|WP_Error
     */
    public function list($project_id) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to list production sessions.', 'tersuite-ai-studio'));
        }

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/production-sessions';
        return $this->api->get($path);
    }

    /**
     * Get specific production session details.
     *
     * @param string|int $project_id
     * @param string|int $session_id
     * @return array|WP_Error
     */
    public function get($project_id, $session_id) {
        if (empty($project_id) || empty($session_id)) {
            return new WP_Error('invalid_params', __('Project ID and Session ID are required.', 'tersuite-ai-studio'));
        }

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/production-sessions/' . rawurlencode((string) $session_id);
        return $this->api->get($path);
    }

    /**
     * Cancel an active production session.
     *
     * @param string|int $project_id
     * @param string|int $session_id
     * @return array|WP_Error
     */
    public function cancel($project_id, $session_id) {
        if (empty($project_id) || empty($session_id)) {
            return new WP_Error('invalid_params', __('Project ID and Session ID are required to cancel a session.', 'tersuite-ai-studio'));
        }

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/production-sessions/' . rawurlencode((string) $session_id) . '/cancel';
        return $this->api->post($path);
    }
}
