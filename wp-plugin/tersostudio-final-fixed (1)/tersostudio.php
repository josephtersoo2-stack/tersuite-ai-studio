<?php
/**
 * Plugin Name: TersoStudio v2 Core
 * Plugin URI: https://example.com
 * Description: Enterprise IoC Kernel and Modular AI Development Operating System Core for WordPress.
 * Version: 2.0.0
 * Author: Joseph Tersoo
 * Text Domain: tersostudio
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TERSOSTUDIO_VERSION', '2.0.0' );
define( 'TERSOSTUDIO_PATH', plugin_dir_path( __FILE__ ) );
define( 'TERSOSTUDIO_URL', plugin_dir_url( __FILE__ ) );
define( 'TERSOSTUDIO_SLUG', 'tersostudio' );

require_once TERSOSTUDIO_PATH . 'core/class-service-container.php';
require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-response-factory.php';
require_once TERSOSTUDIO_PATH . 'core/Security/class-rate-limit-gate.php';
require_once TERSOSTUDIO_PATH . 'core/Security/class-path-validator.php';
require_once TERSOSTUDIO_PATH . 'core/Diagnostics/class-central-exception-handler.php';

// Arm centralized diagnostic exception handling routing vectors
TERSOSTUDIO_Central_Exception_Handler::get_instance();

function tersostudio_initialize_directory_sandbox(): void {
    $upload_dir = wp_upload_dir();
    $base_upload_path = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio';

    $subdirectories = [
        'projects',
        'workspace',
        'snapshots',
        'deployments',
        'training',
        'blueprints',
        'memory',
        'rag',
        'logs',
        'cache',
        'exports',
        'backups',
        'temp'
    ];

    foreach ( $subdirectories as $dir ) {
        $target_path = trailingslashit( $base_upload_path ) . $dir;
        if ( ! is_dir( $target_path ) ) {
            wp_mkdir_p( $target_path );
        }
    }
}
add_action( 'init', 'tersostudio_initialize_directory_sandbox', 1 );

function tersostudio_register_core_infrastructure_singletons( TERSOSTUDIO_Service_Container $container ): void {
    require_once TERSOSTUDIO_PATH . 'core/IO/class-filesystem-gate.php';
    require_once TERSOSTUDIO_PATH . 'core/AI/class-api-gateway.php';
    require_once TERSOSTUDIO_PATH . 'core/Database/class-db-installer.php';
    require_once TERSOSTUDIO_PATH . 'core/Database/class-project-state-repository.php';
    require_once TERSOSTUDIO_PATH . 'core/EventBus/class-event-journal.php';
    require_once TERSOSTUDIO_PATH . 'core/Queue/class-job-orchestrator.php';
    require_once TERSOSTUDIO_PATH . 'core/Queue/class-queue-runner-utility.php';
    require_once TERSOSTUDIO_PATH . 'core/Learning/class-learning-engine.php';
    require_once TERSOSTUDIO_PATH . 'core/Restore/class-restore-point-manager.php';
    require_once TERSOSTUDIO_PATH . 'core/RAG/class-rag-orchestrator.php';
    require_once TERSOSTUDIO_PATH . 'core/RAG/class-context-pruner.php';
    require_once TERSOSTUDIO_PATH . 'core/RAG/class-codebase-analyzer.php';
    require_once TERSOSTUDIO_PATH . 'core/Swarm/class-prompt-composer.php';
    require_once TERSOSTUDIO_PATH . 'core/Swarm/class-swarm-orchestrator.php';
    require_once TERSOSTUDIO_PATH . 'admin/class-admin-controller.php';
    require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-chat-controller.php';
    require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-projects-controller.php';
    require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-settings-controller.php';
    require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-models-controller.php';
    require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-learning-controller.php';
    require_once TERSOSTUDIO_PATH . 'api/v2/class-rest-restore-controller.php';

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

    $container->singleton( 'job_orchestrator', static function() {
        return TERSOSTUDIO_Job_Orchestrator::get_instance();
    } );

    $container->singleton( 'queue_runner_utility', static function() {
        return TERSOSTUDIO_Queue_Runner_Utility::get_instance();
    } );

    $container->singleton( 'learning_engine', static function() {
        return TERSOSTUDIO_Learning_Engine::get_instance();
    } );

    $container->singleton( 'restore_point_manager', static function() {
        return TERSOSTUDIO_Restore_Point_Manager::get_instance();
    } );

    $container->singleton( 'rag_orchestrator', static function() {
        return TERSOSTUDIO_RAG_Orchestrator::get_instance();
    } );

    $container->singleton( 'context_pruner', static function() {
        return TERSOSTUDIO_Context_Pruner::get_instance();
    } );

    $container->singleton( 'prompt_composer', static function() {
        return TERSOSTUDIO_Prompt_Composer::get_instance();
    } );

    $container->singleton( 'swarm_orchestrator', static function() {
        return TERSOSTUDIO_Swarm_Orchestrator::get_instance();
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

    $container->singleton( 'rest_models_controller', static function() {
        return TERSOSTUDIO_REST_Models_Controller::get_instance();
    } );

    $container->singleton( 'rest_learning_controller', static function() {
        return TERSOSTUDIO_REST_Learning_Controller::get_instance();
    } );

    $container->singleton( 'rest_restore_controller', static function() {
        return TERSOSTUDIO_REST_Restore_Controller::get_instance();
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
    $container->make( 'rest_models_controller' );
    $container->make( 'rest_learning_controller' );
    $container->make( 'rest_restore_controller' );
    $container->make( 'job_orchestrator' );
    $container->make( 'queue_runner_utility' );
    $container->make( 'prompt_composer' );
    $container->make( 'swarm_orchestrator' );
}
add_action( 'plugins_loaded', 'tersostudio_kernel_boot_sequence', 10 );

register_activation_hook( __FILE__, static function() {
    tersostudio_initialize_directory_sandbox();
    
    require_once TERSOSTUDIO_PATH . 'core/Database/class-db-installer.php';
    TERSOSTUDIO_DB_Installer::scaffold_tables();
    
    flush_rewrite_rules();
} );
