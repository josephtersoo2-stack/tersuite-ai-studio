<?php
defined('ABSPATH') || exit;

/**
 * Project Manager
 *
 * REST API client wrapper for managing Django projects.
 */
class Tersuite_AI_Project_Manager {

    protected $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    public function list($params = array()) {
        $path = 'api/v1/projects/';
        if (!empty($params)) {
            $path .= '?' . http_build_query($params);
        }
        return $this->api->get($path);
    }

    public function get($id) {
        if (empty($id)) {
            return new WP_Error('missing_id', __('Project ID is required.', 'tersuite-ai-studio'));
        }
        return $this->api->get('api/v1/projects/' . rawurlencode((string) $id));
    }

    public function create($name, $description = '', $type = 'plugin') {
        $payload = array(
            'name'        => sanitize_text_field($name),
            'description' => sanitize_textarea_field($description),
            'type'        => sanitize_key($type),
        );
        return $this->api->post('api/v1/projects/', $payload);
    }

    public function update($id, $data) {
        if (empty($id)) {
            return new WP_Error('missing_id', __('Project ID is required for update.', 'tersuite-ai-studio'));
        }
        return $this->api->put('api/v1/projects/' . rawurlencode((string) $id), $data);
    }

    public function delete($id) {
        if (empty($id)) {
            return new WP_Error('missing_id', __('Project ID is required for deletion.', 'tersuite-ai-studio'));
        }
        return $this->api->delete('api/v1/projects/' . rawurlencode((string) $id));
    }
}
