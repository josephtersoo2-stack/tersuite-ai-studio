<?php
defined('ABSPATH') || exit;

/**
 * Centralized API Client for Tersuite Backend Communication.
 *
 * Responsibilities:
 * - Handle HTTP methods (GET, POST, PUT, DELETE)
 * - Authenticate via Bearer Token
 * - Inject X-Request-ID and X-Tersuite-Client headers
 * - Format JSON bodies and parse HTTP responses
 * - Return structured WP_Error objects on failure with status code and body
 */
class Tersuite_AI_API_Client {

    /**
     * Get configured Backend API Base URL.
     *
     * @return string
     */
    public function base_url() {
        return rtrim((string) Tersuite_AI_Settings::get('backend_api_url'), '/');
    }

    /**
     * Check if backend API URL is configured.
     *
     * @return bool
     */
    public function is_configured() {
        return $this->base_url() !== '';
    }

    /**
     * Send HTTP request to Django backend.
     *
     * @param string     $method HTTP Method (GET, POST, PUT, DELETE)
     * @param string     $path   Relative API Path (e.g. 'api/v1/projects')
     * @param array|null $body   Request payload array to JSON encode
     * @param array      $args   Optional request parameter overrides
     * @return array|WP_Error Parsed JSON response array or WP_Error on failure
     */
    public function request($method, $path, $body = null, $args = array()) {
        if (!$this->is_configured()) {
            return new WP_Error(
                'not_configured',
                __('Backend API URL is not configured. Please visit Tersuite Settings → Connection.', 'tersuite-ai-studio')
            );
        }

        $url = $this->base_url() . '/' . ltrim($path, '/');

        $headers = array(
            'Accept'           => 'application/json',
            'Content-Type'     => 'application/json',
            'X-Tersuite-Client' => 'wordpress-plugin/' . TERSUITE_AI_VERSION,
        );

        $api_key = Tersuite_AI_Settings::get('api_key');
        if (!empty($api_key)) {
            $headers['Authorization'] = 'Bearer ' . $api_key;
        }

        if (function_exists('wp_generate_uuid4')) {
            $headers['X-Request-ID'] = wp_generate_uuid4();
        }

        $timeout = isset($args['timeout']) ? absint($args['timeout']) : 30;

        $request_args = array(
            'method'    => strtoupper($method),
            'timeout'   => $timeout,
            'headers'   => $headers,
            'sslverify' => true,
        );

        if ($body !== null) {
            $request_args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request($url, $request_args);

        if (is_wp_error($response)) {
            return new WP_Error(
                'http_connection_failed',
                sprintf(__('Connection failed: %s', 'tersuite-ai-studio'), $response->get_error_message()),
                array('original_error' => $response->get_error_code())
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $raw_body    = wp_remote_retrieve_body($response);
        $decoded     = json_decode($raw_body, true);

        if ($status_code < 200 || $status_code >= 300) {
            $error_msg = sprintf(__('Backend returned HTTP status code %d.', 'tersuite-ai-studio'), $status_code);
            if (is_array($decoded)) {
                foreach (array('message','detail','error','error_description') as $key) {
                    if (isset($decoded[$key]) && is_scalar($decoded[$key]) && (string)$decoded[$key] !== '') { $error_msg = (string)$decoded[$key]; break; }
                }
            }

            return new WP_Error(
                'backend_api_error',
                $error_msg,
                array(
                    'status' => $status_code,
                    'body'   => $decoded !== null ? $decoded : $raw_body,
                )
            );
        }

        if ($decoded === null && $raw_body !== '') {
            return array('raw' => $raw_body);
        }

        // Some Django/DRF deployments return HTTP 201 with an empty body and
        // a Location header pointing at the newly-created resource. Follow it
        // server-side so the WordPress UI receives the actual resource ID.
        if ($decoded === null && $raw_body === '' && strtoupper($method) === 'POST') {
            $location = wp_remote_retrieve_header($response, 'location');
            if (!empty($location)) {
                $location_path = (string) wp_parse_url($location, PHP_URL_PATH);
                $base_path = (string) wp_parse_url($this->base_url(), PHP_URL_PATH);
                if ($location_path !== '') {
                    if ($base_path !== '' && strpos($location_path, $base_path) === 0) {
                        $location_path = substr($location_path, strlen($base_path));
                    }
                    $location_path = ltrim($location_path, '/');
                    // Only follow API resource URLs; never arbitrary external URLs.
                    if (strpos($location_path, 'api/v1/') === 0) {
                        $followed = $this->get($location_path);
                        if (!is_wp_error($followed) && is_array($followed)) {
                            return $followed;
                        }
                    }
                }
            }
        }

        return is_array($decoded) ? $decoded : array();
    }

    /**
     * Send GET request.
     */
    public function get($path, $args = array()) {
        return $this->request('GET', $path, null, $args);
    }

    /**
     * Send POST request.
     */
    public function post($path, $body = array(), $args = array()) {
        return $this->request('POST', $path, $body, $args);
    }

    /**
     * Send PUT request.
     */
    public function put($path, $body = array(), $args = array()) {
        return $this->request('PUT', $path, $body, $args);
    }

    /**
     * Send DELETE request.
     */
    public function delete($path, $args = array()) {
        return $this->request('DELETE', $path, null, $args);
    }
}
