<?php
defined('ABSPATH') || exit;

/**
 * Activity Manager
 */
class Tersuite_AI_Activity_Manager {

    protected $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    public function list($project_id = null) {
        if (!empty($project_id)) {
            return $this->api->get('api/v1/projects/' . rawurlencode((string) $project_id) . '/activity');
        }
        return $this->api->get('api/v1/activity');
    }
}
