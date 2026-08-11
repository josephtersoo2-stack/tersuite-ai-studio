<?php
defined('ABSPATH') || exit;

class Tersuite_AI_WebSocket_Client {
    public function get_config($project_id = '') {
        $base_url = (string) Tersuite_AI_Settings::get('websocket_base_url');
        if (!$base_url) {
            $backend_url = (string) Tersuite_AI_Settings::get('backend_api_url');
            if ($backend_url) $base_url = preg_replace('#^http:#', 'ws:', preg_replace('#^https:#', 'wss:', rtrim($backend_url, '/')));
        }
        return array(
            'url' => $base_url,
            'channel' => $project_id !== '' ? 'project_'.$project_id : 'global',
            'configured' => $base_url !== '',
            // Never expose the long-lived REST API key to browser JavaScript.
        );
    }
}
