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

    public function register_routes(): void {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'dispatch_chat_request' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/edit', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'modify_historical_chat_string' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/delete', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'purge_historical_chat_row' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/approve', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'approve_blueprint_and_mobilize_devs' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/sync', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'synchronize_active_workspace_runtime_states' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'synchronize_active_workspace_runtime_states' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/queue', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'retrieve_global_jobs_queue_ledger' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/queue/cancel', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'execute_forced_job_kill_switch' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/journal/clear', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'clear_project_journal_logs' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/job-status/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'poll_job_status' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );
    }

    public function verify_access_clearance( WP_REST_Request $request ): bool {
        return current_user_can( 'manage_options' );
    }

    public function dispatch_chat_request( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'dispatch_chat', 30, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached. Too many chat messages requested.', 'rate_limit_exceeded', 429 );
        }

        global $wpdb;
        $chat_table = $wpdb->prefix . 'ts_chat_history';
        $nonce = $request->get_header( 'X-WP-Nonce' ) ?: $request->get_param( 'nonce' );

        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Cryptographic security token attestation failed.', 'security_check_failed', 403 );
        }

        $project_id = intval( $request->get_param( 'project_id' ) );
        $message = sanitize_textarea_field( $request->get_param( 'message' ) );

        if ( empty( $project_id ) || empty( trim( $message ) ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Incomplete parameters.', 'malformed_payload', 400 );
        }

        $wpdb->insert( $chat_table, [ 'project_id' => $project_id, 'sender_role' => 'user', 'message_body' => $message, 'token_count' => 0 ], [ '%d', '%s', '%s', '%d' ] );
        $orchestrator = TERSOSTUDIO_Service_Container::get_instance()->make( 'job_orchestrator' );
        $spawn_result = $orchestrator->spawn_job( $project_id, 'architect' );

        if ( ! $spawn_result['success'] ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $spawn_result['message'], $spawn_result['code'], 500 );
        }

        return TERSOSTUDIO_REST_Response_Factory::success( 'Architect analysis job provisioned successfully.', [ 'job_id' => $spawn_result['data']['job_id'] ], 202 );
    }

    public function modify_historical_chat_string( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'modify_chat', 30, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        global $wpdb;
        $params = $request->get_json_params();
        $msg_id = intval( $params['id'] ?? 0 );
        $text   = sanitize_textarea_field( $params['message'] ?? '' );

        if ( empty( $msg_id ) || empty( $text ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Invalid structural payload criteria variables.', 'malformed_payload', 400 );
        }

        $wpdb->update( $wpdb->prefix . 'ts_chat_history', [ 'message_body' => $text ], [ 'id' => $msg_id ], [ '%s' ], [ '%d' ] );
        return TERSOSTUDIO_REST_Response_Factory::success( 'Timeline message successfully committed to database.' );
    }

    public function purge_historical_chat_row( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'purge_chat', 30, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        global $wpdb;
        $params = $request->get_json_params();
        $msg_id = intval( $params['id'] ?? 0 );

        if ( empty( $msg_id ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Target identification index dropped.', 'malformed_payload', 400 );
        }

        $wpdb->delete( $wpdb->prefix . 'ts_chat_history', [ 'id' => $msg_id ], [ '%d' ] );
        return TERSOSTUDIO_REST_Response_Factory::success( 'Message successfully removed from datastore.' );
    }

    public function approve_blueprint_and_mobilize_devs( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'approve_blueprint', 20, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        $params = $request->get_json_params();
        $project_id = intval( $params['project_id'] ?? 0 );

        if ( empty( $project_id ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Project context identifier missing.', 'malformed_payload', 400 );
        }

        $orchestrator = TERSOSTUDIO_Service_Container::get_instance()->make( 'job_orchestrator' );
        $spawn_result = $orchestrator->spawn_job( $project_id, 'developer' );

        if ( ! $spawn_result['success'] ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $spawn_result['message'], $spawn_result['code'], 500 );
        }

        return TERSOSTUDIO_REST_Response_Factory::success( 'Blueprint approved. Swarm systems engineers mobilized.', [ 'job_id' => $spawn_result['data']['job_id'] ], 202 );
    }

    public function synchronize_active_workspace_runtime_states( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'sync_workspace', 60, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        global $wpdb;
        $project_id = intval( $request->get_param( 'project_id' ) );

        if ( empty( $project_id ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Missing workspace project tracking scope ID parameter.', 'malformed_payload', 400 );
        }

        $repo = TERSOSTUDIO_Service_Container::get_instance()->make( 'project_state_repo' );

        if ( 'POST' === $request->get_method() ) {
            $file_path = sanitize_text_field( (string) $request->get_param( 'file_path' ) );
            $file_content = (string) $request->get_param( 'file_content' );

            if ( empty( $file_path ) ) {
                return TERSOSTUDIO_REST_Response_Factory::error( 'Missing file_path for workspace persistence operation.', 'malformed_payload', 400 );
            }

            $validator = TERSOSTUDIO_Path_Validator::get_instance();
            $upload_dir = wp_upload_dir();
            $sandbox_root = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/workspace/project-' . $project_id . '/';
            $absolute_target = wp_normalize_path( $sandbox_root . ltrim( $file_path, '/' ) );

            if ( ! $validator->is_path_safe( $absolute_target ) ) {
                return TERSOSTUDIO_REST_Response_Factory::error( 'Requested file path violates workspace sandbox containment boundaries.', 'path_violation', 403 );
            }

            if ( $repo ) {
                $save_result = $repo->save_workspace_file( $project_id, $file_path, $file_content );
                if ( ! $save_result['success'] ) {
                    return TERSOSTUDIO_REST_Response_Factory::error( $save_result['message'], $save_result['code'], 500 );
                }
            }

            $fs = TERSOSTUDIO_Service_Container::get_instance()->make( 'filesystem_gate' );
            if ( $fs ) {
                $fs->write( $absolute_target, $file_content );
            }

            return TERSOSTUDIO_REST_Response_Factory::success(
                'Workspace file content synchronized successfully.',
                [ 'file_path' => $file_path, 'file_content' => $file_content ]
            );
        }

        $chat_table = $wpdb->prefix . 'ts_chat_history';
        $chat_query = $wpdb->prepare( "SELECT id, sender_role, message_body FROM {$chat_table} WHERE project_id = %d ORDER BY id ASC", $project_id );
        $chat_data = $wpdb->get_results( $chat_query, ARRAY_A ) ?: [];
        $files_data = [];

        if ( $repo ) {
            $files_res = $repo->get_workspace_files( $project_id );
            $files_data = $files_res['data']['files'] ?? [];
        }

        return TERSOSTUDIO_REST_Response_Factory::success( 'Workspace dataset layers synchronized.', [ 'chat' => $chat_data, 'files' => $files_data ] );
    }

    public function retrieve_global_jobs_queue_ledger( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'get_queue', 60, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        global $wpdb;
        $query = "SELECT j.*, p.name as project_name FROM {$wpdb->prefix}ts_jobs j "
               . "LEFT JOIN {$wpdb->prefix}ts_projects p ON j.project_id = p.id "
               . "ORDER BY j.id DESC LIMIT 50";
        $jobs = $wpdb->get_results( $query, ARRAY_A ) ?: [];

        return TERSOSTUDIO_REST_Response_Factory::success( 'Global jobs queue ledger retrieved.', [ 'queue' => $jobs ] );
    }

    public function execute_forced_job_kill_switch( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'kill_job', 20, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        global $wpdb;
        $params = $request->get_json_params();
        $job_id = intval( $params['job_id'] ?? 0 );

        if ( empty( $job_id ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Missing target task sequence reference context.', 'malformed_payload', 400 );
        }

        $wpdb->update( $wpdb->prefix . 'ts_jobs', [ 'status' => 'failed' ], [ 'id' => $job_id ], [ '%s' ], [ '%d' ] );
        $wpdb->insert( $wpdb->prefix . 'ts_event_journal', [
            'job_id'       => $job_id,
            'event_name'   => 'ts_job.forced_terminated',
            'payload_data' => wp_json_encode( [ 'error' => 'CRITICAL KILL-SWITCH: Background worker pipeline terminated manually by cluster admin.' ] ),
            'logged_at'    => current_time( 'mysql' )
        ], [ '%d', '%s', '%s', '%s' ] );

        return TERSOSTUDIO_REST_Response_Factory::success( 'Background agent operation thread terminated.' );
    }

    public function clear_project_journal_logs( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'clear_logs', 20, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        global $wpdb;
        $params = $request->get_json_params();
        $project_id = intval( $params['project_id'] ?? 0 );

        if ( empty( $project_id ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Project parameters missing.', 'malformed_payload', 400 );
        }

        $jobs_query = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ts_jobs WHERE project_id = %d", $project_id );
        $job_ids = $wpdb->get_col( $jobs_query );

        if ( ! empty( $job_ids ) ) {
            $job_ids_in = implode( ',', array_map( 'intval', $job_ids ) );
            $wpdb->query( "DELETE FROM {$wpdb->prefix}ts_event_journal WHERE job_id IN ($job_ids_in)" );
        }

        return TERSOSTUDIO_REST_Response_Factory::success( 'Project journal history logs wiped successfully.' );
    }

    public function poll_job_status( WP_REST_Request $request ): WP_REST_Response {
        // High Frequency Allowances Gate: Yields 120 polling requests per window constraints explicitly
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'poll_status', 120, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Throttling active: Polling threshold reached.', 'rate_limit_exceeded', 429 );
        }

        $job_id = intval( $request->get_param( 'id' ) );
        $orchestrator = TERSOSTUDIO_Service_Container::get_instance()->make( 'job_orchestrator' );

        if ( ! $orchestrator ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Background queue module unavailable.', 'orchestrator_offline', 500 );
        }

        $status_result = $orchestrator->query_job_status( $job_id );
        if ( ! $status_result['success'] ) {
            return TERSOSTUDIO_REST_Response_Factory::error( $status_result['message'], $status_result['code'], 404 );
        }

        return TERSOSTUDIO_REST_Response_Factory::success( 'Background transaction state rehydrated successfully.', $status_result['data'] );
    }
}
