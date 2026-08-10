<?php
/**
 * API Client for communicating with Django backend.
 * Handles REST requests, authentication, and error handling.
 */
final class APIClient {
    private string $base_url;
    private string $api_key;

    public function __construct() {
        $this->base_url = rtrim(get_option('tersuite_api_base', 'http://localhost:8000/api/'), '/');
        $this->api_key = sanitize_text_field(get_option('tersuite_api_key', ''));
    }

    public function get(string $endpoint, array $params = []): array {
        return $this->request('GET', $endpoint, $params);
    }

    public function post(string $endpoint, array $data = []): array {
        return $this->request('POST', $endpoint, $data);
    }

    public function delete(string $endpoint): array {
        return $this->request('DELETE', $endpoint);
    }

    private function request(string $method, string $endpoint, array $data = []): array {
        $url = $this->base_url . '/' . ltrim($endpoint, '/');
        $args = [
            'method' => $method,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Token ' . $this->api_key,
            ],
            'timeout' => 30,
        ];
        if ($method === 'POST') {
            $args['body'] = json_encode($data);
        }
        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return ['status' => 'error', 'message' => $response->get_error_message()];
        }
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        if ($code >= 400) {
            return is_array($decoded) ? $decoded : ['status' => 'error', 'message' => 'Backend HTTP error ' . $code];
        }
        return is_array($decoded) ? $decoded : ['status' => 'unknown', 'raw' => $body];
    }
}
