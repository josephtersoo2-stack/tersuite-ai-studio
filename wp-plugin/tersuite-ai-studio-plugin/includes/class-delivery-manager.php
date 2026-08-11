<?php
defined('ABSPATH') || exit;

/**
 * Delivery Manager
 */
class Tersuite_AI_Delivery_Manager {

    protected $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    public function list($project_id) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to list deliveries.', 'tersuite-ai-studio'));
        }
        return $this->api->get('api/v1/projects/' . rawurlencode((string) $project_id) . '/deliveries');
    }

    public function deliver($project_id) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required.', 'tersuite-ai-studio'));
        }
        return $this->api->post('api/v1/projects/' . rawurlencode((string) $project_id) . '/deliver');
    }
}
