<?php
/**
 * Plugin Name: TersoStudio Client
 * Plugin URI: https://github.com/josephtersoo2-stack/tersuite-ai-studio
 * Description: WordPress Client Bridge for Tersuite AI Studio Backend (Gemini 3.6 & CrewAI Pipeline).
 * Version: 2.1.0
 * Author: Joseph Tersoo
 * Text Domain: tersostudio
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TERSOSTUDIO_VERSION', '2.1.0' );
define( 'TERSOSTUDIO_PATH', plugin_dir_path( __FILE__ ) );
define( 'TERSOSTUDIO_URL', plugin_dir_url( __FILE__ ) );
define( 'TERSOSTUDIO_SLUG', 'tersostudio' );

require_once TERSOSTUDIO_PATH . 'core/class-service-container.php';
require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-response-factory.php';
require_once TERSOSTUDIO_PATH . 'core/Security/class-rate-limit-gate.php';
require_once TERSOSTUDIO_PATH . 'core/Security/class-path-validator.php';
require_once TERSOSTUDIO_PATH . 'core/Diagnostics/class-central-exception-handler.php';

TERSOSTUDIO_Central_Exception_Handler::get_instance();

function tersostudio_register_core_infrastructure_singletons( TERSOSTUDIO_Service_Container $container ): void {
    require_once TERSOSTUDIO_PATH . 'core/IO/class-filesystem-gate.php';
    require_once TERSOSTUDIO_PATH . 'core/AI/class-api-gateway.php';
    require_once TERSOSTUDIO_PATH . 'core/Database/class-db-installer.php';
    require_once TERSOSTUDIO_PATH . 'core/Database/class-project-state-repository.php';
    require_once TERSOSTUDIO_PATH . 'core/EventBus/class-event-journal.php';
    require_once TERSOSTUDIO_PATH . 'admin/class-admin-controller.php';
    require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-chat-controller.php';
    require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-projects-controller.php';
    require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-settings-controller.php';

    $container->singleton( 'filesystem_gate', static function() {
        return TERSOSTUDIO_Filesystem_Gate::get_instance();
    } );

    $container->singleton( 'api_gateway', static function() {
        return TERSOSTUDIO_AI_Gateway::get_instance();
    } );

    $container->singleton( 'project_state_repo', static function() {
        return TERSOSTUDIO_Project_State_Repository::get_instance();
    } );

    $container->singleton( 'event_journal', static function() {
        return TERSOSTUDIO_Event_Journal::get_instance();
    } );

    $container->singleton( 'admin_controller', static function() {
        return TERSOSTUDIO_Admin_Controller::get_instance();
    } );

    $container->singleton( 'rest_chat_controller', static function() {
        return TERSOSTUDIO_REST_Chat_Controller::get_instance();
    } );

    $container->singleton( 'rest_projects_controller', static function() {
        return TERSOSTUDIO_REST_Projects_Controller::get_instance();
    } );

    $container->singleton( 'rest_settings_controller', static function() {
        return TERSOSTUDIO_REST_Settings_Controller::get_instance();
    } );
}
add_action( 'tersostudio_core_kernel_loaded', 'tersostudio_register_core_infrastructure_singletons', 1 );

function tersostudio_kernel_boot_sequence(): void {
    $container = TERSOSTUDIO_Service_Container::get_instance();
    
    do_action( 'tersostudio_core_kernel_loaded', $container );

    $container->make( 'admin_controller' );
    $container->make( 'rest_chat_controller' );
    $container->make( 'rest_projects_controller' );
    $container->make( 'rest_settings_controller' );
}
add_action( 'plugins_loaded', 'tersostudio_kernel_boot_sequence', 10 );

register_activation_hook( __FILE__, static function() {
    require_once TERSOSTUDIO_PATH . 'core/Database/class-db-installer.php';
    TERSOSTUDIO_DB_Installer::scaffold_tables();
    flush_rewrite_rules();
} );
