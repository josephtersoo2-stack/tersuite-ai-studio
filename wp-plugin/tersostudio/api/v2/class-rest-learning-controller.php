<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_REST_Learning_Controller extends WP_REST_Controller {
    private static ?TERSOSTUDIO_REST_Learning_Controller $instance = null;

    private function __construct() {
        $this->namespace = 'tersostudio/v2';
        $this->rest_base = 'learning';
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public static function get_instance(): TERSOSTUDIO_REST_Learning_Controller {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_routes(): void {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_learned_patterns' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );
    }

    public function verify_access_clearance( WP_REST_Request $request ): bool {
        return current_user_can( 'manage_options' );
    }

    public function get_learned_patterns( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;
        $debug_id = 'ts_api_' . wp_generate_password( 8, false, false );
        try {
            $table = $wpdb->prefix . 'ts_error_memory';
            
            // Enforce Strict SQL Prepare Rule on learning retrieval routes
            $query = $wpdb->prepare( "SELECT id, file_path, error_signature, learned_at FROM {$table} ORDER BY id DESC LIMIT 40", [] );
            $results = $wpdb->get_results( $query, ARRAY_A );

            return new WP_REST_Response( [
                'success'  => true,
                'message'  => 'Self-healing error memory tracking sets fetched successfully.',
                'code'     => 'success',
                'debug_id' => $debug_id,
                'data'     => [ 'patterns' => is_array( $results ) ? $results : [] ]
            ], 200 );
        } catch ( \Throwable $e ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage(), 'code' => 'internal_server_error', 'debug_id' => $debug_id, 'data' => [] ], 500 );
        }
    }
}
