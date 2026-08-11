<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Admin_Controller {
    private static ?TERSOSTUDIO_Admin_Controller $instance = null;
    private string $parent_slug = 'tersostudio';
    private string $capability = 'manage_options';

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_admin_submenu_hierarchy' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_workbench_assets' ] );
    }

    public static function get_instance(): TERSOSTUDIO_Admin_Controller {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_admin_submenu_hierarchy(): void {
        add_menu_page(
            'TersoStudio v2',
            'TersoStudio',
            $this->capability,
            $this->parent_slug,
            [ $this, 'render_command_center_view' ],
            'dashicons-superhero',
            100
        );

        $submenus = [
            'tersostudio-workbench'   => [ 'label' => 'Workspace IDE', 'callback' => 'render_workspace_ide_view' ],
            'tersostudio-projects'    => [ 'label' => 'Projects Registry', 'callback' => 'render_projects_registry_view' ],
            'tersostudio-swarm'       => [ 'label' => 'Agents Swarm', 'callback' => 'render_agents_swarm_view' ],
            'tersostudio-models'      => [ 'label' => 'Model Catalogues', 'callback' => 'render_model_catalogues_view' ],
            'tersostudio-knowledge'   => [ 'label' => 'Knowledge Base', 'callback' => 'render_knowledge_view' ],
            'tersostudio-queue'       => [ 'label' => 'Queue Monitor', 'callback' => 'render_queue_monitor_view' ],
            'tersostudio-restore'     => [ 'label' => 'Restore Points', 'callback' => 'render_restore_points_view' ],
            'tersostudio-deployments' => [ 'label' => 'Deployments Panel', 'callback' => 'render_deployments_view' ],
            'tersostudio-security'    => [ 'label' => 'API & Security', 'callback' => 'render_security_view' ],
            'tersostudio-logs'        => [ 'label' => 'System Logs', 'callback' => 'render_logs_view' ],
            'tersostudio-settings'    => [ 'label' => 'Global Settings', 'callback' => 'render_settings_view' ],
            'tersostudio-learning'    => [ 'label' => 'Learning Engine', 'callback' => 'render_learning_engine_view' ]
        ];

        add_submenu_page( $this->parent_slug, 'Command Center', 'Command Center', $this->capability, $this->parent_slug, [ $this, 'render_command_center_view' ] );

        foreach ( $submenus as $slug => $meta ) {
            add_submenu_page( $this->parent_slug, $meta['label'], $meta['label'], $this->capability, $slug, [ $this, $meta['callback'] ] );
        }
    }

    public function enqueue_workbench_assets( string $hook ): void {
        if ( strpos( $hook, 'tersostudio' ) === false ) {
            return;
        }

        wp_dequeue_script( 'tersostudio-legacy-chat' );
        wp_deregister_script( 'tersostudio-legacy-chat' );

        wp_enqueue_media();
        wp_enqueue_script( 'wp-element' );
        
        // Command WordPress core to initialize its CodeMirror asset configurations for PHP structures
        $editor_settings = wp_enqueue_code_editor( array( 'type' => 'text/x-php' ) );
        wp_enqueue_style( 'wp-codemirror' );
        
        wp_enqueue_script( 'tersostudio-workbench-js', TERSOSTUDIO_URL . 'admin/build/index.js', [ 'wp-element', 'jquery' ], TERSOSTUDIO_VERSION, true );

        $project_id = isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0;
        $files_data = [];
        $chat_data = [];
        $terminal_persisted_history_logs = [];

        if ( $project_id > 0 ) {
            $repo = TERSOSTUDIO_Service_Container::get_instance()->make( 'project_state_repo' );
            if ( $repo ) {
                $files_res = $repo->get_workspace_files( $project_id );
                $files_data = $files_res['data']['files'] ?? [];
                
                global $wpdb;
                $chat_table = $wpdb->prefix . 'ts_chat_history';
                $chat_query = $wpdb->prepare( "SELECT id, sender_role, message_body FROM {$chat_table} WHERE project_id = %d ORDER BY id ASC", $project_id );
                $chat_data = $wpdb->get_results( $chat_query, ARRAY_A ) ?: [];

                $jobs_query = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ts_jobs WHERE project_id = %d", $project_id );
                $job_ids = $wpdb->get_col( $jobs_query );
                if ( ! empty( $job_ids ) ) {
                    $job_ids_in_sanitized = implode( ',', array_map( 'intval', $job_ids ) );
                    $journal_rows = $wpdb->get_results( "SELECT event_name, payload_data, logged_at FROM {$wpdb->prefix}ts_event_journal WHERE job_id IN ($job_ids_in_sanitized) ORDER BY id ASC", ARRAY_A );
                    if ( is_array( $journal_rows ) ) {
                        foreach ( $journal_rows as $row ) {
                            $payload = json_decode( $row['payload_data'], true );
                            $msg = $payload['msg'] ?? $payload['error'] ?? $payload['file'] ?? 'Task step executed.';
                            $time_prefix = date( 'H:i:s', strtotime( $row['logged_at'] ) );
                            $terminal_persisted_history_logs[] = "[{$time_prefix}] " . strtoupper( $row['event_name'] ) . " -> " . $msg;
                        }
                    }
                }
            }
        }

        if ( empty( $terminal_persisted_history_logs ) ) {
            $terminal_persisted_history_logs[] = '[' . date('H:i:s') . '] [System Core Initialized]: Handshake established with project environment layer.';
        }

        wp_localize_script( 'tersostudio-workbench-js', 'TERSOSTUDIO_State', [
            'ajaxurl'        => admin_url( 'admin-ajax.php' ),
            'rest_url'       => esc_url_raw( rest_url( 'tersostudio/v2' ) ),
            'nonce'          => wp_create_nonce( 'wp_rest' ),
            'projectId'      => $project_id,
            'currentFiles'   => $files_data,
            'chatHistory'    => $chat_data,
            'terminalLogs'   => $terminal_persisted_history_logs,
            'editorSettings' => $editor_settings
        ] );
    }

    public function render_command_center_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-command-center.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Command Center view template layout asset missing.</p></div></div>';
        }
    }

    public function render_workspace_ide_view(): void {
        echo '<div class="wrap tersostudio-workbench-canvas-root" style="margin:0;padding:0;"><div id="tersostudio-workbench-root"></div></div>';
    }

    public function render_projects_registry_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-projects-registry.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Projects registry view module missing. Structural synchronization required.</p></div></div>';
        }
    }

    public function render_agents_swarm_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-agents-swarm.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Agents swarm panel view template missing. Structural synchronization required.</p></div></div>';
        }
    }

    public function render_model_catalogues_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-model-catalogues.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Model Catalogues database panel view template missing. Structural synchronization required.</p></div></div>';
        }
    }

    public function render_queue_monitor_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-queue.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Queue Monitor view layout component missing.</p></div></div>';
        }
    }

    public function render_learning_engine_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-learning-engine.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Learning Engine view template asset missing. Structural synchronization required.</p></div></div>';
        }
    }

    public function render_restore_points_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-restore-points.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Restore Points snapshot view template asset missing. Structural synchronization required.</p></div></div>';
        }
    }

    public function render_deployments_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-deployments.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Deployments view asset layout component missing.</p></div></div>';
        }
    }

    public function render_security_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-security.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Security Sentinel view layout component missing.</p></div></div>';
        }
    }

    public function render_logs_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-logs.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>System Logs layout component template missing.</p></div></div>';
        }
    }

    public function render_knowledge_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-knowledge.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Knowledge Base (RAG) template view component missing.</p></div></div>';
        }
    }

    public function render_settings_view(): void {
        $file = TERSOSTUDIO_PATH . 'admin/views/view-global-settings.php';
        if ( $this->view_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>Global Settings control page template layout asset missing.</p></div></div>';
        }
    }

    private function view_exists( string $file ): bool {
        $fs = TERSOSTUDIO_Service_Container::get_instance()->make( 'filesystem_gate' );
        return $fs ? $fs->exists( $file ) : false;
    }
}