<?php
defined('ABSPATH') || exit;

/**
 * Version Manager
 */
class Tersuite_AI_Version_Manager {

    protected $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    public function list($project_id) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to list versions.', 'tersuite-ai-studio'));
        }
        return $this->api->get('api/v1/projects/' . rawurlencode((string) $project_id) . '/versions');
    }

    public function restore($project_id, $version_id) {
        if (empty($project_id) || empty($version_id)) {
            return new WP_Error('invalid_params', __('Project ID and Version ID are required.', 'tersuite-ai-studio'));
        }
        return $this->api->post('api/v1/projects/' . rawurlencode((string) $project_id) . '/versions/' . rawurlencode((string) $version_id) . '/restore');
    }
}
