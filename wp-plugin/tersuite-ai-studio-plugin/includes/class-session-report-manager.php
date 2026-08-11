<?php
defined('ABSPATH') || exit;

/**
 * Session Report Manager
 *
 * Fetches durable session completion reports from Django for UI presentation.
 */
class Tersuite_AI_Session_Report_Manager {

    private $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    /**
     * List session reports for a project.
     *
     * @param string|int $project_id
     * @return array|WP_Error
     */
    public function list($project_id) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to fetch session reports.', 'tersuite-ai-studio'));
        }

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/session-reports';
        return $this->api->get($path);
    }

    /**
     * Get a specific session completion report.
     *
     * @param string|int $project_id
     * @param string|int $session_id
     * @return array|WP_Error
     */
    public function get($project_id, $session_id) {
        if (empty($project_id) || empty($session_id)) {
            return new WP_Error('invalid_params', __('Project ID and Session ID are required.', 'tersuite-ai-studio'));
        }

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/session-reports/' . rawurlencode((string) $session_id);
        return $this->api->get($path);
    }
}
