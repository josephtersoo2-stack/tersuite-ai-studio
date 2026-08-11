<?php
defined('ABSPATH') || exit;

/**
 * Notification Manager
 */
class Tersuite_AI_Notification_Manager {

    protected $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    public function list() {
        return $this->api->get('api/v1/notifications');
    }

    public function mark_read($id) {
        if (empty($id)) {
            return new WP_Error('missing_id', __('Notification ID is required.', 'tersuite-ai-studio'));
        }
        return $this->api->post('api/v1/notifications/' . rawurlencode((string) $id) . '/read');
    }
}
