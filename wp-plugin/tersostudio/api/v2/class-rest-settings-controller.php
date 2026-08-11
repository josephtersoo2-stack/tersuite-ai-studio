<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_REST_Settings_Controller extends WP_REST_Controller {
    private static ?TERSOSTUDIO_REST_Settings_Controller $instance = null;

    private function __construct() {
        $this->namespace = 'tersostudio/v2';
        $this->rest_base = 'settings';
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public static function get_instance(): TERSOSTUDIO_REST_Settings_Controller {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_routes(): void {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'retrieve_settings' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'update_settings' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/test-connection', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'test_backend_connection' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );
    }

    public function verify_access_clearance( WP_REST_Request $request ): bool {
        return current_user_can( 'manage_options' );
    }

    public function retrieve_settings( WP_REST_Request $request ): WP_REST_Response {
        $settings = [
            'backend_url' => (string) get_option( 'tersostudio_backend_url', 'http://127.0.0.1:8000/api' ),
            'api_key'     => (string) get_option( 'tersostudio_api_key', 'ec33c4db14d5bffcc6d3c8c0e81595e3bd020622' ),
        ];

        return TERSOSTUDIO_REST_Response_Factory::success( 'Settings fetched successfully.', [ 'settings' => $settings ] );
    }

    public function update_settings( WP_REST_Request $request ): WP_REST_Response {
        $params = (array) $request->get_json_params();

        if ( isset( $params['backend_url'] ) ) {
            update_option( 'tersostudio_backend_url', sanitize_text_field( (string) $params['backend_url'] ) );
        }
        if ( isset( $params['api_key'] ) ) {
            update_option( 'tersostudio_api_key', sanitize_text_field( (string) $params['api_key'] ) );
        }

        return TERSOSTUDIO_REST_Response_Factory::success( 'Backend connection settings updated successfully.' );
    }

    public function test_backend_connection( WP_REST_Request $request ): WP_REST_Response {
        $backend_url = trailingslashit( trim( (string) get_option( 'tersostudio_backend_url', 'http://127.0.0.1:8000/api' ) ) );
        $api_key     = trim( (string) get_option( 'tersostudio_api_key', 'ec33c4db14d5bffcc6d3c8c0e81595e3bd020622' ) );

        $response = wp_remote_get( $backend_url . 'projects/', [
            'headers' => [ 'Authorization' => 'Token ' . $api_key ],
            'timeout' => 10,
        ] );

        if ( is_wp_error( $response ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Connection Failed: ' . $response->get_error_message(), 'connection_failed', 500 );
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code >= 200 && $code < 300 ) {
            return TERSOSTUDIO_REST_Response_Factory::success( 'Django Backend API Connection Verified Successfully! (HTTP ' . $code . ')' );
        }

        return TERSOSTUDIO_REST_Response_Factory::error( 'API Authorization Error (HTTP Code ' . $code . '). Please check API Key credentials.', 'auth_error', $code );
    }
}
