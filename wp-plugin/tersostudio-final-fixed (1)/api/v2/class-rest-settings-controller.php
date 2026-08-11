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

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/reset', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'factory_reset_system_matrix' ],
                'permission_callback' => [ $this, 'verify_access_clearance' ]
            ]
        ] );
    }

    public function verify_access_clearance( WP_REST_Request $request ): bool {
        return current_user_can( 'manage_options' );
    }

    public function retrieve_settings( WP_REST_Request $request ): WP_REST_Response {
        $settings = [
            'gemini_key'          => (string) get_option( 'tersostudio_gemini_key', '' ),
            'openai_key'          => (string) get_option( 'tersostudio_openai_key', '' ),
            'claude_key'          => (string) get_option( 'tersostudio_claude_key', '' ),
            'architect_simple'    => (string) get_option( 'tersostudio_agent_architect_simple', '' ),
            'architect_reasoning' => (string) get_option( 'tersostudio_agent_architect_reasoning', '' ),
            'backend_engineer'    => (string) get_option( 'tersostudio_agent_backend_engineer', '' ),
            'frontend_engineer'   => (string) get_option( 'tersostudio_agent_frontend_engineer', '' ),
            'database_architect'  => (string) get_option( 'tersostudio_agent_database_architect', '' ),
            'security_auditor'    => (string) get_option( 'tersostudio_agent_security_auditor', '' ),
            'patch_engine'        => (string) get_option( 'tersostudio_agent_patch_engine', '' ),
            'memory_orchestrator' => (string) get_option( 'tersostudio_agent_memory_orchestrator', '' ),
            'qa_validation'       => (string) get_option( 'tersostudio_agent_qa_validation', '' ),
            'devops_monitor'      => (string) get_option( 'tersostudio_agent_devops_monitor', '' ),
            'learning_specialist' => (string) get_option( 'tersostudio_agent_learning_specialist', '' ),
            'security_nogo_zones' => (string) get_option( 'tersostudio_security_nogo_zones', "wp-config.php\n.env\nwp-admin\nwp-includes" ),
            'max_file_size'       => (int) get_option( 'tersostudio_max_file_size', 1024 ),
            'rate_limit_poll'     => (int) get_option( 'tersostudio_rate_limit_poll', 120 ),
            'vacuum_interval'     => (int) get_option( 'tersostudio_vacuum_interval', 300 )
        ];

        return TERSOSTUDIO_REST_Response_Factory::success( 'API operational vectors rehydrated safely from infrastructure options.', [ 'settings' => $settings ] );
    }

    public function update_settings( WP_REST_Request $request ): WP_REST_Response {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Cryptographic token attestation failed.', 'security_check_failed', 403 );
        }

        $params = (array) $request->get_json_params();

        $model_fields = [
            'architect_simple', 'architect_reasoning', 'backend_engineer', 
            'frontend_engineer', 'database_architect', 'security_auditor', 
            'patch_engine', 'memory_orchestrator', 'qa_validation', 
            'devops_monitor', 'learning_specialist'
        ];

        foreach ( $model_fields as $field ) {
            if ( array_key_exists( $field, $params ) && '' === trim( (string) $params[ $field ] ) ) {
                return TERSOSTUDIO_REST_Response_Factory::error( 'Model configurations cannot be blank. Assign all 10 specialized swarm roles explicitly.', 'missing_model_configuration', 400 );
            }
        }

        if ( array_key_exists( 'gemini_key', $params ) ) update_option( 'tersostudio_gemini_key', sanitize_text_field( (string) $params['gemini_key'] ) );
        if ( array_key_exists( 'openai_key', $params ) ) update_option( 'tersostudio_openai_key', sanitize_text_field( (string) $params['openai_key'] ) );
        if ( array_key_exists( 'claude_key', $params ) ) update_option( 'tersostudio_claude_key', sanitize_text_field( (string) $params['claude_key'] ) );

        if ( array_key_exists( 'architect_simple', $params ) )    update_option( 'tersostudio_agent_architect_simple', sanitize_text_field( (string) $params['architect_simple'] ) );
        if ( array_key_exists( 'architect_reasoning', $params ) ) update_option( 'tersostudio_agent_architect_reasoning', sanitize_text_field( (string) $params['architect_reasoning'] ) );
        if ( array_key_exists( 'backend_engineer', $params ) )    update_option( 'tersostudio_agent_backend_engineer', sanitize_text_field( (string) $params['backend_engineer'] ) );
        if ( array_key_exists( 'frontend_engineer', $params ) )   update_option( 'tersostudio_agent_frontend_engineer', sanitize_text_field( (string) $params['frontend_engineer'] ) );
        if ( array_key_exists( 'database_architect', $params ) )  update_option( 'tersostudio_agent_database_architect', sanitize_text_field( (string) $params['database_architect'] ) );
        if ( array_key_exists( 'security_auditor', $params ) )    update_option( 'tersostudio_agent_security_auditor', sanitize_text_field( (string) $params['security_auditor'] ) );
        if ( array_key_exists( 'patch_engine', $params ) )        update_option( 'tersostudio_agent_patch_engine', sanitize_text_field( (string) $params['patch_engine'] ) );
        if ( array_key_exists( 'memory_orchestrator', $params ) ) update_option( 'tersostudio_agent_memory_orchestrator', sanitize_text_field( (string) $params['memory_orchestrator'] ) );
        if ( array_key_exists( 'qa_validation', $params ) )       update_option( 'tersostudio_agent_qa_validation', sanitize_text_field( (string) $params['qa_validation'] ) );
        if ( array_key_exists( 'devops_monitor', $params ) )      update_option( 'tersostudio_agent_devops_monitor', sanitize_text_field( (string) $params['devops_monitor'] ) );
        if ( array_key_exists( 'learning_specialist', $params ) ) update_option( 'tersostudio_agent_learning_specialist', sanitize_text_field( (string) $params['learning_specialist'] ) );

        if ( isset( $params['security_nogo_zones'] ) )   update_option( 'tersostudio_security_nogo_zones', sanitize_textarea_field( (string) $params['security_nogo_zones'] ) );
        if ( isset( $params['max_file_size'] ) )         update_option( 'tersostudio_max_file_size', intval( $params['max_file_size'] ) );
        if ( isset( $params['rate_limit_poll'] ) )       update_option( 'tersostudio_rate_limit_poll', intval( $params['rate_limit_poll'] ) );
        if ( isset( $params['vacuum_interval'] ) )       update_option( 'tersostudio_vacuum_interval', intval( $params['vacuum_interval'] ) );

        return TERSOSTUDIO_REST_Response_Factory::success( 'Ecosystem operational credentials updated and validated successfully.' );
    }

    public function factory_reset_system_matrix( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return TERSOSTUDIO_REST_Response_Factory::error( 'Security token check failed.', 'security_check_failed', 403 );
        }

        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}ts_projects" );
        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}ts_workspace_files" );
        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}ts_jobs" );
        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}ts_event_journal" );
        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}ts_chat_history" );
        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}ts_snapshots" );
        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}ts_logs" );
        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}ts_error_memory" );

        $fs = TERSOSTUDIO_Service_Container::get_instance()->make( 'filesystem_gate' );
        if ( $fs ) {
            $upload_dir = wp_upload_dir();
            $fs->delete( trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/' );
            wp_mkdir_p( trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/' );
        }

        return TERSOSTUDIO_REST_Response_Factory::success( 'Factory reset sequence executed. All operational tables and workspaces cleared.' );
    }
}
