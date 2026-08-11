<?php
defined('ABSPATH') || exit;

/**
 * Project Memory Manager
 *
 * Interacts with Django's authoritative project memory.
 * Never stores project memory in local WordPress database tables.
 */
class Tersuite_AI_Project_Memory_Manager {

    private $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    /**
     * Get project memory from Django.
     *
     * @param string|int $project_id
     * @return array|WP_Error
     */
    public function get($project_id) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to fetch memory.', 'tersuite-ai-studio'));
        }

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/memory';
        return $this->api->get($path);
    }

    /**
     * Append project memory entry through Django.
     *
     * @param string|int $project_id
     * @param array      $memory_data
     * @return array|WP_Error
     */
    public function append($project_id, $memory_data = array()) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to append memory.', 'tersuite-ai-studio'));
        }

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/memory';
        return $this->api->post($path, array('memory' => $memory_data));
    }
}
