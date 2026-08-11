<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_REST_Projects_Controller extends WP_REST_Controller {
    private static ?TERSOSTUDIO_REST_Projects_Controller $instance = null;

    private function __construct() {
        $this->namespace = 'tersostudio/v2';
        $this->rest_base = 'projects';
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public static function get_instance(): TERSOSTUDIO_REST_Projects_Controller {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_routes(): void {
        // Projects Routes
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_projects' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_project' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[a-zA-Z0-9-]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_project' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        // Categories Routes
        register_rest_route( $this->namespace, '/categories', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_categories' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_category' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/categories/(?P<id>[a-zA-Z0-9-]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_category' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );
    }

    public function verify_access_clearance( WP_REST_Request $request ): bool {
        return current_user_can( 'manage_options' );
    }

    private function get_backend_config(): array {
        return [
            'url'   => trailingslashit( trim( get_option( 'tersostudio_backend_url', 'http://localhost:8000/api' ) ) ),
            'token' => trim( get_option( 'tersostudio_api_key', 'ec33c4db14d5bffcc6d3c8c0e81595e3bd020622' ) ),
        ];
    }

    public function get_projects( WP_REST_Request $request ): WP_REST_Response {
        $config = $this->get_backend_config();
        $response = wp_remote_get( $config['url'] . 'projects/', [
            'headers' => [ 'Authorization' => 'Token ' . $config['token'] ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $response->get_error_message(), 'connection_error', 500 );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return rest_ensure_response( $body );
    }

    public function create_project( WP_REST_Request $request ): WP_REST_Response {
        $config = $this->get_backend_config();
        $params = $request->get_json_params() ?: $request->get_params();

        $response = wp_remote_post( $config['url'] . 'projects/', [
            'headers' => [
                'Authorization' => 'Token ' . $config['token'],
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $params ),
            'timeout' => 20,
        ] );

        if ( is_wp_error( $response ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $response->get_error_message(), 'connection_error', 500 );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return new WP_REST_Response( $body, $code );
    }

    public function delete_project( WP_REST_Request $request ): WP_REST_Response {
        $config = $this->get_backend_config();
        $id = $request->get_param( 'id' );

        $response = wp_remote_request( $config['url'] . 'projects/' . $id . '/', [
            'method'  => 'DELETE',
            'headers' => [ 'Authorization' => 'Token ' . $config['token'] ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $response->get_error_message(), 'connection_error', 500 );
        }

        return rest_ensure_response( [ 'success' => true ] );
    }

    public function get_categories( WP_REST_Request $request ): WP_REST_Response {
        $config = $this->get_backend_config();
        $response = wp_remote_get( $config['url'] . 'categories/', [
            'headers' => [ 'Authorization' => 'Token ' . $config['token'] ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $response->get_error_message(), 'connection_error', 500 );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return rest_ensure_response( $body );
    }

    public function create_category( WP_REST_Request $request ): WP_REST_Response {
        $config = $this->get_backend_config();
        $params = $request->get_json_params() ?: $request->get_params();

        $response = wp_remote_post( $config['url'] . 'categories/', [
            'headers' => [
                'Authorization' => 'Token ' . $config['token'],
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $params ),
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $response->get_error_message(), 'connection_error', 500 );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return new WP_REST_Response( $body, $code );
    }

    public function delete_category( WP_REST_Request $request ): WP_REST_Response {
        $config = $this->get_backend_config();
        $id = $request->get_param( 'id' );

        $response = wp_remote_request( $config['url'] . 'categories/' . $id . '/', [
            'method'  => 'DELETE',
            'headers' => [ 'Authorization' => 'Token ' . $config['token'] ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $response->get_error_message(), 'connection_error', 500 );
        }

        return rest_ensure_response( [ 'success' => true ] );
    }
}
