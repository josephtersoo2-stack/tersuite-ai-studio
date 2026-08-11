<?php
defined('ABSPATH') || exit;

/**
 * Task Graph Manager
 *
 * Exposes the backend's dependency-aware task execution graph to the WordPress UI for read-only rendering.
 */
class Tersuite_AI_Task_Graph_Manager {

    private $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    /**
     * Get the backend task graph state for a project.
     *
     * @param string|int $project_id
     * @return array|WP_Error
     */
    public function get($project_id) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to fetch task graph.', 'tersuite-ai-studio'));
        }

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/task-graph';
        return $this->api->get($path);
    }
}
