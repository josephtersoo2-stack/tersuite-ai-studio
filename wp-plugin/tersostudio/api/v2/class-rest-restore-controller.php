<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_REST_Restore_Controller extends WP_REST_Controller {
    private static ?TERSOSTUDIO_REST_Restore_Controller $instance = null;

    private function __construct() {
        $this->namespace = 'tersostudio/v2';
        $this->rest_base = 'restore';
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public static function get_instance(): TERSOSTUDIO_REST_Restore_Controller {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_routes(): void {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_snapshots_history' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'trigger_git_snapshot_action' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );
    }

    public function verify_access_clearance( WP_REST_Request $request ): bool {
        return current_user_can( 'manage_options' );
    }

    public function get_snapshots_history( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;
        $debug_id = 'ts_api_' . wp_generate_password( 8, false, false );
        try {
            $table = $wpdb->prefix . 'ts_snapshots';
            $project_id = intval( $request->get_param( 'project_id' ) );

            if ( empty( $project_id ) ) {
                return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing project target index constraints.', 'code' => 'missing_project_id', 'debug_id' => $debug_id, 'data' => [] ], 400 );
            }

            $query = $wpdb->prepare( "SELECT id, snapshot_name, snapshot_path, created_at FROM {$table} WHERE project_id = %d ORDER BY id DESC", $project_id );
            $results = $wpdb->get_results( $query, ARRAY_A );

            return new WP_REST_Response( [
                'success'  => true,
                'message'  => 'Historical recovery logs rehydrated successfully.',
                'code'     => 'success',
                'debug_id' => $debug_id,
                'data'     => [
                    'snapshots'   => is_array( $results ) ? $results : [],
                    'policy_max'  => intval( get_option( 'tersostudio_policy_max_snapshots', 20 ) ),
                    'policy_days' => intval( get_option( 'tersostudio_policy_expiry_days', 30 ) )
                ]
            ], 200 );
        } catch ( \Throwable $e ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage(), 'code' => 'internal_server_error', 'debug_id' => $debug_id, 'data' => [] ], 500 );
        }
    }

    public function trigger_git_snapshot_action( WP_REST_Request $request ): WP_REST_Response {
        $debug_id = 'ts_api_' . wp_generate_password( 8, false, false );
        try {
            $nonce = $request->get_header( 'X-WP-Nonce' );
            if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                return new WP_REST_Response( [ 'success' => false, 'message' => 'Cryptographic security token validation failed.', 'code' => 'security_check_failed', 'debug_id' => $debug_id, 'data' => [] ], 403 );
            }

            $params = $request->get_json_params();
            $project_id = intval( $params['project_id'] ?? 0 );
            $action_type = sanitize_key( $params['action_type'] ?? '' );
            $name = sanitize_text_field( $params['snapshot_name'] ?? '' );
            $slug = sanitize_text_field( $params['snapshot_slug'] ?? '' );

            if ( 'save_policy' === $action_type ) {
                update_option( 'tersostudio_policy_max_snapshots', intval( $params['policy_max'] ) );
                update_option( 'tersostudio_policy_expiry_days', intval( $params['policy_days'] ) );
                return new WP_REST_Response( [ 'success' => true, 'message' => 'Retention threshold rules committed successfully.', 'code' => 'success', 'debug_id' => $debug_id, 'data' => [] ], 200 );
            }

            if ( empty( $project_id ) ) {
                return new WP_REST_Response( [ 'success' => false, 'message' => 'Malformed parameters matrix: project identity missing.', 'code' => 'malformed_payload', 'debug_id' => $debug_id, 'data' => [] ], 400 );
            }

            $manager = TERSOSTUDIO_Service_Container::get_instance()->make( 'restore_point_manager' );
            if ( ! $manager ) {
                return new WP_REST_Response( [ 'success' => false, 'message' => 'Snapshot Manager subsystem offline.', 'code' => 'subsystem_offline', 'debug_id' => $debug_id, 'data' => [] ], 500 );
            }

            if ( 'create' === $action_type ) {
                if ( empty( $name ) ) $name = 'Manual State Capture Loop - ' . current_time( 'mysql' );
                $res = $manager->create_git_snapshot( $project_id, $name );
                return new WP_REST_Response( [ 'success' => (bool)($res['success'] ?? false), 'message' => $res['message'] ?? '', 'code' => $res['code'] ?? 'success', 'debug_id' => $debug_id, 'data' => $res['data'] ?? [] ], !empty($res['success']) ? 201 : 500 );
            } elseif ( 'restore' === $action_type ) {
                if ( empty( $slug ) ) {
                    return new WP_REST_Response( [ 'success' => false, 'message' => 'Target archive slug required.', 'code' => 'missing_slug', 'debug_id' => $debug_id, 'data' => [] ], 400 );
                }
                $res = $manager->restore_git_snapshot( $project_id, $slug );
                return new WP_REST_Response( [ 'success' => (bool)($res['success'] ?? false), 'message' => $res['message'] ?? '', 'code' => $res['code'] ?? 'success', 'debug_id' => $debug_id, 'data' => $res['data'] ?? [] ], !empty($res['success']) ? 200 : 500 );
            } elseif ( 'delete' === $action_type ) {
                if ( empty( $slug ) ) {
                    return new WP_REST_Response( [ 'success' => false, 'message' => 'Target delete context slug required.', 'code' => 'missing_slug', 'debug_id' => $debug_id, 'data' => [] ], 400 );
                }
                $res = $manager->delete_git_snapshot( $project_id, $slug );
                return new WP_REST_Response( [ 'success' => (bool)($res['success'] ?? false), 'message' => $res['message'] ?? '', 'code' => $res['code'] ?? 'success', 'debug_id' => $debug_id, 'data' => $res['data'] ?? [] ], 200 );
            }

            return new WP_REST_Response( [ 'success' => false, 'message' => 'Invalid action context operational command type route.', 'code' => 'invalid_action', 'debug_id' => $debug_id, 'data' => [] ], 400 );
        } catch ( \Throwable $e ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage(), 'code' => 'internal_server_error', 'debug_id' => $debug_id, 'data' => [] ], 500 );
        }
    }
}
