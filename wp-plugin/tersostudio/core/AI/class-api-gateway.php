<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_AI_Gateway {
    private static ?TERSOSTUDIO_AI_Gateway $instance = null;

    private function __construct() {}

    public static function get_instance(): TERSOSTUDIO_AI_Gateway {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_backend_url(): string {
        $url = get_option( 'tersostudio_backend_url', 'http://127.0.0.1:8000/api' );
        return trailingslashit( trim( $url ) );
    }

    public function get_api_key(): string {
        return trim( (string) get_option( 'tersostudio_api_key', 'ec33c4db14d5bffcc6d3c8c0e81595e3bd020622' ) );
    }

    public function dispatch_task( string $project_id, string $prompt, string $model = 'gemini-3.6-flash' ): array {
        $backend_url = $this->get_backend_url();
        $api_key     = $this->get_api_key();

        $endpoint = $backend_url . 'projects/';
        if ( ! empty( $project_id ) ) {
            $endpoint .= $project_id . '/start/';
        }

        $response = wp_remote_post( $endpoint, [
            'headers' => [
                'Authorization' => 'Token ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( [
                'prompt' => $prompt,
                'model'  => $model,
            ] ),
            'timeout' => 60,
        ] );

        if ( is_wp_error( $response ) ) {
            return [
                'success' => false,
                'message' => 'Backend Connection Error: ' . $response->get_error_message(),
            ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code >= 200 && $code < 300 ) {
            return [
                'success' => true,
                'data'    => $body,
            ];
        }

        return [
            'success' => false,
            'message' => $body['detail'] ?? $body['message'] ?? 'Backend API Error (HTTP ' . $code . ')',
        ];
    }
}
