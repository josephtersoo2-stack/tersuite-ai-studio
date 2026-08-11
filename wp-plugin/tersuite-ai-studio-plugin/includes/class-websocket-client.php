<?php
defined('ABSPATH') || exit;

/**
 * WebSocket Client Manager
 *
 * Resolves server-side WebSocket connection details (URL, channel, ticket)
 * for real-time telemetry streaming from Django to the browser client.
 */
class Tersuite_AI_WebSocket_Client {

    /**
     * Get WebSocket connection configuration.
     *
     * @param string|int $project_id
     * @return array
     */
    public function get_config($project_id = '') {
        $base_url = (string) Tersuite_AI_Settings::get('websocket_base_url');
        if (empty($base_url)) {
            $backend_url = (string) Tersuite_AI_Settings::get('backend_api_url');
            if (!empty($backend_url)) {
                $base_url = str_replace(array('http://', 'https://'), array('ws://', 'wss://'), rtrim($backend_url, '/'));
            }
        }

        $api_key = Tersuite_AI_Settings::get('api_key');

        return array(
            'url'        => $base_url,
            'channel'    => !empty($project_id) ? 'project_' . $project_id : 'global',
            'api_key'    => !empty($api_key) ? $api_key : '',
            'configured' => !empty($base_url),
        );
    }
}
