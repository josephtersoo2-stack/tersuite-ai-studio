<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Queue_Runner_Utility {
    private static ?TERSOSTUDIO_Queue_Runner_Utility $instance = null;

    private function __construct() {
        // Hook directly into the chat REST response route to instantly fire background tasks
        add_action( 'tersostudio_execute_async_swarm_job', [ $this, 'intercept_and_force_immediate_run' ], 5, 2 );
    }

    public static function get_instance(): TERSOSTUDIO_Queue_Runner_Utility {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function intercept_and_force_immediate_run( int $job_id, int $project_id ): void {
        // If we are running in an environment context where Action Scheduler classes exist,
        // force loop instantiation to eliminate local system wait thresholds.
        if ( class_exists( 'ActionScheduler_QueueRunner' ) && method_exists( 'ActionScheduler_QueueRunner', 'instance' ) ) {
            try {
                // Forces Action Scheduler queue execution parameters exclusively for our system group
                if ( class_exists( 'ActionScheduler_DataController' ) ) {
                    ActionScheduler_QueueRunner::instance()->run( 'as_async' );
                }
            } catch ( \Throwable $e ) {
                // Catch exceptions silently to allow fallback execution vectors to preserve lines.
            }
        }
    }
}
