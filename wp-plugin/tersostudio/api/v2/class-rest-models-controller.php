<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_REST_Models_Controller extends WP_REST_Controller {
    private static ?TERSOSTUDIO_REST_Models_Controller $instance = null;

    private function __construct() {
        $this->namespace = 'tersostudio/v2';
        $this->rest_base = 'models';
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public static function get_instance(): TERSOSTUDIO_REST_Models_Controller {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_routes(): void {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'retrieve_registered_models' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'commit_bulk_models_catalog' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );
    }

    public function verify_access_clearance( WP_REST_Request $request ): bool {
        return current_user_can( 'manage_options' );
    }

    public function retrieve_registered_models( WP_REST_Request $request ): WP_REST_Response {
        $debug_id = 'ts_api_' . wp_generate_password( 8, false, false );

        try {
            $raw_catalog = trim( (string) get_option( 'tersostudio_models_catalog_dictionary', '' ) );

            $parsed_models = [];
            if ( '' !== $raw_catalog ) {
                $lines = explode( "\n", str_replace( "\r", "", $raw_catalog ) );
                foreach ( $lines as $line ) {
                    $line = trim( $line );
                    if ( empty( $line ) || strpos( $line, '|' ) === false ) {
                        continue;
                    }
                    list( $id, $name ) = explode( '|', $line, 2 );
                    $parsed_models[] = [ 'id' => trim( $id ), 'name' => trim( $name ) ];
                }
            }

            return new WP_REST_Response( [
                'success'  => true,
                'message'  => '' === $raw_catalog ? 'Model catalogue is empty. Add entries from the Models page.' : 'Dynamic database model tracking lists hydrated successfully.',
                'code'     => 'success',
                'debug_id' => $debug_id,
                'data'     => [ 'models' => $parsed_models, 'raw_textarea' => $raw_catalog ]
            ], 200 );
        } catch ( \Throwable $e ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage(), 'code' => 'internal_server_error', 'debug_id' => $debug_id, 'data' => [] ], 500 );
        }
    }

    public function commit_bulk_models_catalog( WP_REST_Request $request ): WP_REST_Response {
        $debug_id = 'ts_api_' . wp_generate_password( 8, false, false );
        try {
            $nonce = $request->get_header( 'X-WP-Nonce' );
            if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                return new WP_REST_Response( [ 'success' => false, 'message' => 'Cryptographic security token validation failed.', 'code' => 'security_check_failed', 'debug_id' => $debug_id, 'data' => [] ], 403 );
            }

            $raw_text = trim( (string) $request->get_param( 'raw_catalog_text' ) );
            if ( '' === $raw_text ) {
                return new WP_REST_Response( [ 'success' => false, 'message' => 'Model catalogue cannot be blank. Add at least one model entry line.', 'code' => 'missing_model_catalogue', 'debug_id' => $debug_id, 'data' => [] ], 400 );
            }

            update_option( 'tersostudio_models_catalog_dictionary', sanitize_textarea_field( $raw_text ) );

            return new WP_REST_Response( [
                'success'  => true,
                'message'  => 'Model catalogue dictionary layout re-compiled and locked into database storage.',
                'code'     => 'success',
                'debug_id' => $debug_id,
                'data'     => []
            ], 200 );
        } catch ( \Throwable $e ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage(), 'code' => 'internal_server_error', 'debug_id' => $debug_id, 'data' => [] ], 500 );
        }
    }
}
