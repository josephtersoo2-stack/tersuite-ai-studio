<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_REST_Chat_Controller extends WP_REST_Controller {
    private static ?TERSOSTUDIO_REST_Chat_Controller $instance = null;

    private function __construct() {
        $this->namespace = 'tersostudio/v2';
        $this->rest_base = 'chat';
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public static function get_instance(): TERSOSTUDIO_REST_Chat_Controller {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function get_backend_config(): array {
        return [
            'url'   => trailingslashit( trim( get_option( 'tersostudio_backend_url', 'http://127.0.0.1:8000/api' ) ) ),
            'token' => trim( get_option( 'tersostudio_api_key', 'ec33c4db14d5bffcc6d3c8c0e81595e3bd020622' ) ),
        ];
    }

    public function register_routes(): void {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'dispatch_chat_request' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/stream/(?P<project_id>[a-zA-Z0-9-]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'stream_project_progress' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/deliver/(?P<project_id>[a-zA-Z0-9-]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'deliver_project_files' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );
    }

    public function verify_access_clearance( WP_REST_Request $request ): bool {
        return current_user_can( 'manage_options' );
    }

    public function dispatch_chat_request( WP_REST_Request $request ): WP_REST_Response {
        $config = $this->get_backend_config();
        $params = $request->get_json_params() ?: $request->get_params();
        $project_id = sanitize_text_field( $params['project_id'] ?? $request->get_param( 'project_id' ) ?? '' );
        $message = sanitize_textarea_field( $params['message'] ?? $request->get_param( 'message' ) ?? '' );

        if ( empty( $project_id ) || empty( trim( $message ) ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Project ID and message prompt are required.', 'malformed_payload', 400 );
        }

        $response = wp_remote_post( $config['url'] . 'projects/' . $project_id . '/start/', [
            'headers' => [
                'Authorization' => 'Token ' . $config['token'],
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( [ 'task' => $message ] ),
            'timeout' => 30,
        ] );

        if ( is_wp_error( $response ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $response->get_error_message(), 'connection_error', 500 );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return new WP_REST_Response( $body, $code );
    }

    public function stream_project_progress( WP_REST_Request $request ): WP_REST_Response {
        $config = $this->get_backend_config();
        $project_id = sanitize_text_field( $request->get_param( 'project_id' ) );

        $response = wp_remote_get( $config['url'] . 'projects/' . $project_id . '/stream/', [
            'headers' => [ 'Authorization' => 'Token ' . $config['token'] ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $response->get_error_message(), 'connection_error', 500 );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return new WP_REST_Response( $body, $code );
    }

    public function deliver_project_files( WP_REST_Request $request ): WP_REST_Response {
        $config = $this->get_backend_config();
        $project_id = sanitize_text_field( $request->get_param( 'project_id' ) );

        $response = wp_remote_get( $config['url'] . 'projects/' . $project_id . '/stream/', [
            'headers' => [ 'Authorization' => 'Token ' . $config['token'] ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $response->get_error_message(), 'connection_error', 500 );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return new WP_REST_Response( $body, $code );
    }
}
