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
            'TersoStudio AI',
            'TersoStudio',
            $this->capability,
            $this->parent_slug,
            [ $this, 'render_command_center_view' ],
            'dashicons-superhero',
            100
        );

        $submenus = [
            'tersostudio-workbench' => [ 'label' => 'Workspace IDE', 'callback' => 'render_workspace_ide_view' ],
            'tersostudio-projects'  => [ 'label' => 'Projects Registry', 'callback' => 'render_projects_registry_view' ],
            'tersostudio-security'  => [ 'label' => 'API & Credentials', 'callback' => 'render_security_view' ],
            'tersostudio-logs'      => [ 'label' => 'System Logs', 'callback' => 'render_logs_view' ],
            'tersostudio-settings'  => [ 'label' => 'Global Settings', 'callback' => 'render_settings_view' ],
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

        wp_enqueue_media();
        wp_enqueue_script( 'wp-element' );
        wp_enqueue_code_editor( array( 'type' => 'text/x-php' ) );
        wp_enqueue_style( 'wp-codemirror' );
        wp_enqueue_script( 'tersostudio-workbench-js', TERSOSTUDIO_URL . 'admin/build/index.js', [ 'wp-element', 'jquery' ], TERSOSTUDIO_VERSION, true );
    }

    public function render_command_center_view(): void {
        require_once TERSOSTUDIO_PATH . 'admin/views/view-command-center.php';
    }

    public function render_workspace_ide_view(): void {
        echo '<div id="tersostudio-workbench-root"></div>';
    }

    public function render_projects_registry_view(): void {
        require_once TERSOSTUDIO_PATH . 'admin/views/view-projects-registry.php';
    }

    public function render_security_view(): void {
        require_once TERSOSTUDIO_PATH . 'admin/views/view-security.php';
    }

    public function render_logs_view(): void {
        require_once TERSOSTUDIO_PATH . 'admin/views/view-logs.php';
    }

    public function render_settings_view(): void {
        require_once TERSOSTUDIO_PATH . 'admin/views/view-global-settings.php';
    }
}