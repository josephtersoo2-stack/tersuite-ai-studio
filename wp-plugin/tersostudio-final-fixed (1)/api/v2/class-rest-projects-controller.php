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

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/upload', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'upload_project_zip' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/delete', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'execute_cascading_project_deletion' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );
    }

    public function verify_access_clearance( WP_REST_Request $request ): bool {
        return current_user_can( 'manage_options' );
    }

    public function get_projects( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'get_projects', 45, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ts_projects';
        
        $query = $wpdb->prepare( "SELECT id, name, slug, category_slug, created_at FROM {$table} ORDER BY id DESC", [] );
        $results = $wpdb->get_results( $query, ARRAY_A );

        $wp_categories = get_categories( [ 'hide_empty' => false ] );
        $formatted_cats = [];
        foreach ( $wp_categories as $cat ) {
            $formatted_cats[] = [ 'slug' => $cat->slug, 'name' => $cat->name ];
        }

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins_list = [];
        foreach ( get_plugins() as $plugin_path => $plugin_meta ) {
            $plugins_list[] = [ 'path' => $plugin_path, 'name' => $plugin_meta['Name'], 'folder' => dirname( $plugin_path ) ];
        }

        return TERSOSTUDIO_REST_Response_Factory::success( 'Project lists fetched successfully.', [ 'projects' => is_array( $results ) ? $results : [], 'categories' => $formatted_cats, 'plugins' => $plugins_list ] );
    }

    public function create_project( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'create_project', 15, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Security check failed.', 'security_check_failed', 403 );
        }

        $name = sanitize_text_field( $request->get_param( 'name' ) );
        $slug = sanitize_title( $request->get_param( 'slug' ) ?: $name );
        $category_slug = sanitize_key( $request->get_param( 'category_slug' ) ?: 'uncategorized' );
        $source_plugin = sanitize_text_field( $request->get_param( 'source_plugin' ) );

        if ( empty( $name ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Project Name required.', 'missing_parameters', 400 );
        }

        $container = TERSOSTUDIO_Service_Container::get_instance();
        $repo = $container->make( 'project_state_repo' );
        $fs   = $container->make( 'filesystem_gate' );

        $result = $repo->create_project( $name, $slug, $category_slug );
        if ( ! $result['success'] ) return TERSOSTUDIO_REST_Response_Factory::error( $result['message'], 'db_error', 500 );

        $project_id = $result['data']['project_id'];
        
        try {
            $upload_dir = wp_upload_dir();
            $sandbox_path = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/workspace/project-' . $project_id . '/';
            $fs->mkdir( $sandbox_path );

            if ( ! empty( $source_plugin ) && '.' !== $source_plugin ) {
                require_once TERSOSTUDIO_PATH . 'core/Database/class-project-ingestion-service.php';
                TERSOSTUDIO_Project_Ingestion_Service::get_instance()->process_local_directory_ingestion( $project_id, $source_plugin, $sandbox_path, $slug );
            }
        } catch ( \Throwable $e ) {
            $this->perform_emergency_rollback( $project_id );
            return TERSOSTUDIO_REST_Response_Factory::error( 'Staging process exception encountered. Workspace creation aborted and rolled back securely: ' . $e->getMessage(), 'ingestion_failure', 500 );
        }

        return TERSOSTUDIO_REST_Response_Factory::success( 'Project workspace state provisioned successfully.', [ 'project_id' => $project_id ], 211 );
    }

    public function upload_project_zip( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'upload_zip', 10, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Security check failed.', 'security_check_failed', 403 );
        }

        $name = sanitize_text_field( $request->get_param( 'name' ) );
        $slug = sanitize_title( $request->get_param( 'slug' ) ?: $name );
        $category_slug = sanitize_key( $request->get_param( 'category_slug' ) ?: 'uncategorized' );

        if ( empty( $name ) || empty( $_FILES['plugin_zip'] ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Project context parameters or script zip archives omitted.', 'missing_parameters', 400 );
        }

        $file = $_FILES['plugin_zip'];
        
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $file_check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
        $ext  = empty( $file_check['ext'] ) ? pathinfo( $file['name'], PATHINFO_EXTENSION ) : $file_check['ext'];
        $type = empty( $file_check['type'] ) ? '' : $file_check['type'];
        
        $allowed_zip_mimes = [ 'application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-zip' ];
        if ( 'zip' !== strtolower( $ext ) || ( ! empty( $type ) && ! in_array( $type, $allowed_zip_mimes, true ) ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Invalid upload formatting contract: file must pass strict application/zip content attestation verification.', 'invalid_format', 400 );
        }

        $container = TERSOSTUDIO_Service_Container::get_instance();
        $repo = $container->make( 'project_state_repo' );
        $fs   = $container->make( 'filesystem_gate' );

        $result = $repo->create_project( $name, $slug, $category_slug );
        if ( ! $result['success'] ) return TERSOSTUDIO_REST_Response_Factory::error( $result['message'], 'db_error', 500 );

        $project_id = $result['data']['project_id'];
        
        try {
            $upload_dir = wp_upload_dir();
            $sandbox_path = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/workspace/project-' . $project_id . '/';
            $fs->mkdir( $sandbox_path );

            require_once TERSOSTUDIO_PATH . 'core/Database/class-project-ingestion-service.php';
            $status = TERSOSTUDIO_Project_Ingestion_Service::get_instance()->process_zip_package_ingestion( $project_id, $file, $sandbox_path, $slug );

            if ( ! $status ) {
                throw new \Exception( 'Package decompressed block structure synchronization fault encountered.' );
            }
        } catch ( \Throwable $e ) {
            $this->perform_emergency_rollback( $project_id );
            return TERSOSTUDIO_REST_Response_Factory::error( 'ZIP file extraction ingestion failed. Fragmented assets rolled back securely: ' . $e->getMessage(), 'ingestion_fault', 500 );
        }

        return TERSOSTUDIO_REST_Response_Factory::success( 'ZIP context successfully uploaded, stripped of asset noise, and indexed to RAG tables.', [ 'project_id' => $project_id ], 201 );
    }

    private function perform_emergency_rollback( int $project_id ): void {
        global $wpdb;
        $container = TERSOSTUDIO_Service_Container::get_instance();
        $fs = $container->make( 'filesystem_gate' );
        $upload_dir = wp_upload_dir();
        
        if ( $fs ) {
            $fs->delete( trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/workspace/project-' . $project_id . '/', true );
            $fs->delete( trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/snapshots/project-' . $project_id . '/', true );
        }
        
        $wpdb->delete( $wpdb->prefix . 'ts_projects', [ 'id' => $project_id ], [ '%d' ] );
        $wpdb->delete( $wpdb->prefix . 'ts_workspace_files', [ 'project_id' => $project_id ], [ '%d' ] );
        $wpdb->delete( $wpdb->prefix . 'ts_chat_history', [ 'project_id' => $project_id ], [ '%d' ] );
        $wpdb->delete( $wpdb->prefix . 'ts_snapshots', [ 'project_id' => $project_id ], [ '%d' ] );
        
        $job_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ts_jobs WHERE project_id = %d", $project_id ) );
        if ( ! empty( $job_ids ) ) {
            $wpdb->query( "DELETE FROM {$wpdb->prefix}ts_event_journal WHERE job_id IN (" . implode( ',', array_map( 'intval', $job_ids ) ) . ")" );
        }
        $wpdb->delete( $wpdb->prefix . 'ts_jobs', [ 'project_id' => $project_id ], [ '%d' ] );
    }

    public function execute_cascading_project_deletion( WP_REST_Request $request ): WP_REST_Response {
        if ( TERSOSTUDIO_Rate_Limit_Gate::check_throttle( 'delete_project', 15, 60 ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Rate limit reached.', 'rate_limit_exceeded', 429 );
        }

        global $wpdb;
        $params = $request->get_json_params();
        $project_id = intval( $params['project_id'] ?? 0 );
        
        if ( empty( $project_id ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Missing project target constraints.', 'missing_parameters', 400 );
        }
        
        $this->perform_emergency_rollback( $project_id );
        return TERSOSTUDIO_REST_Response_Factory::success( 'Project workspace dropped cleanly from relational databases.' );
    }
}
