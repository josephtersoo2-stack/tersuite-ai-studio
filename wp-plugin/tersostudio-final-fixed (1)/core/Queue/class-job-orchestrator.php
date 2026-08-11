<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Job_Orchestrator {
    private static ?TERSOSTUDIO_Job_Orchestrator $instance = null;
    private string $jobs_table;
    private string $journal_table;

    private function __construct() {
        global $wpdb;
        $this->jobs_table   = $wpdb->prefix . 'ts_jobs';
        $this->journal_table = $wpdb->prefix . 'ts_event_journal';
        add_action( 'tersostudio_execute_async_swarm_job', [ $this, 'process_async_scheduler_loop' ], 10, 2 );
    }

    public static function get_instance(): TERSOSTUDIO_Job_Orchestrator {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function find_active_job_for_project( int $project_id ): array {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT * FROM {$this->jobs_table} WHERE project_id = %d AND status IN ('pending','running') ORDER BY id DESC LIMIT 1",
            $project_id
        );
        $job = $wpdb->get_row( $query, ARRAY_A );
        return is_array( $job ) ? $job : [];
    }

    public function spawn_job( int $project_id, string $initial_agent_slug ): array {
        global $wpdb;
        $debug_id = 'ts_job_' . wp_generate_password( 8, false, false );

        $this->vacuum_stalled_jobs();

        $active_job = $this->find_active_job_for_project( $project_id );
        if ( ! empty( $active_job['id'] ) ) {
            return [
                'success'  => true,
                'message'  => 'An active background thread already exists for this project. Reusing current job lane.',
                'code'     => 'job_already_active',
                'debug_id' => $debug_id,
                'data'     => [ 'job_id' => intval( $active_job['id'] ) ]
            ];
        }

        $now = current_time( 'mysql' );
        $result = $wpdb->insert(
            $this->jobs_table,
            [
                'project_id'          => $project_id,
                'status'              => 'pending',
                'progress_percentage' => 0,
                'active_agent'        => sanitize_key( $initial_agent_slug ),
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [ '%d', '%s', '%d', '%s', '%s', '%s' ]
        );

        $job_id = intval( $wpdb->insert_id );
        if ( ! $job_id || false === $result ) {
            return [
                'success'  => false,
                'message'  => 'Failed to allocate system cluster job thread identifier mapping.',
                'code'     => 'allocation_failed',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        $journal = TERSOSTUDIO_Service_Container::get_instance()->make( 'event_journal' );
        if ( $journal ) {
            $journal->record_event( $job_id, 'ts_job.provisioned', [ 'project_id' => $project_id, 'agent' => $initial_agent_slug ] );
        }

        if ( function_exists( 'as_schedule_single_action' ) ) {
            as_schedule_single_action( time(), 'tersostudio_execute_async_swarm_job', [ 'job_id' => $job_id, 'project_id' => $project_id ], 'tersostudio-group' );
        } else {
            wp_schedule_single_event( time(), 'tersostudio_execute_async_swarm_job', [ 'job_id' => $job_id, 'project_id' => $project_id ] );
        }

        return [
            'success'  => true,
            'message'  => 'Asynchronous transaction thread initialized and offloaded to core worker scheduling arrays.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => [ 'job_id' => $job_id ]
        ];
    }

    public function advance_job_state( int $job_id, string $status, int $progress, ?string $active_agent = null ): void {
        global $wpdb;
        $data = [
            'status'              => sanitize_key( $status ),
            'progress_percentage' => min( max( $progress, 0 ), 100 ),
            'updated_at'          => current_time( 'mysql' ),
        ];
        $format = [ '%s', '%d', '%s' ];

        if ( null !== $active_agent ) {
            $data['active_agent'] = sanitize_key( $active_agent );
            $format[] = '%s';
        }

        $wpdb->update( $this->jobs_table, $data, [ 'id' => $job_id ], $format, [ '%d' ] );
    }

    public function query_job_status( int $job_id ): array {
        global $wpdb;
        $debug_id = 'ts_job_' . wp_generate_password( 8, false, false );

        $query = $wpdb->prepare( "SELECT * FROM {$this->jobs_table} WHERE id = %d", $job_id );
        $job = $wpdb->get_row( $query, ARRAY_A );

        if ( empty( $job ) ) {
            return [
                'success'  => false,
                'message'  => 'The requested async job tracking index does not exist in the infrastructure storage layer.',
                'code'     => 'job_not_found',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        $journal = TERSOSTUDIO_Service_Container::get_instance()->make( 'event_journal' );
        $history_result = $journal ? $journal->fetch_job_history( $job_id ) : [ 'data' => [ 'history' => [] ] ];

        return [
            'success'  => true,
            'message'  => 'Background transaction state rehydrated successfully.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => [
                'job_id'      => intval( $job['id'] ),
                'status'      => $job['status'],
                'progress'    => intval( $job['progress_percentage'] ),
                'agent'       => $job['active_agent'],
                'created_at'  => $job['created_at'] ?? '',
                'updated_at'  => $job['updated_at'] ?? '',
                'task_stream' => $history_result['data']['history'] ?? []
            ]
        ];
    }

    /**
     * Watchdog Engine: Sweeps the job ledger and automatically terminates hanging threads.
     */
    public function vacuum_stalled_jobs(): void {
        global $wpdb;

        $stalled_query = $wpdb->prepare(
            "SELECT id FROM {$this->jobs_table} WHERE status IN ('running', 'pending') AND created_at < (NOW() - INTERVAL 15 MINUTE) LIMIT 20",
            []
        );
        $stalled_job_ids = $wpdb->get_col( $stalled_query );

        if ( ! empty( $stalled_job_ids ) ) {
            foreach ( $stalled_job_ids as $id ) {
                $job_id = intval( $id );
                $this->advance_job_state( $job_id, 'failed', 0 );

                $wpdb->insert( $this->journal_table, [
                    'job_id'       => $job_id,
                    'event_name'   => 'ts_watchdog.timeout_reclaimed',
                    'payload_data' => wp_json_encode( [ 'error' => 'QUEUE WATCHDOG: Reclaimed thread lane. Task marked as failed due to background worker timeout exhaustion constraints.' ] ),
                    'logged_at'    => current_time( 'mysql' )
                ], [ '%d', '%s', '%s', '%s' ] );
            }
        }
    }

    public function process_async_scheduler_loop( int $job_id, int $project_id ): void {
        $this->advance_job_state( $job_id, 'running', 10 );
        $journal = TERSOSTUDIO_Service_Container::get_instance()->make( 'event_journal' );

        if ( $journal ) {
            $journal->record_event( $job_id, 'ts_swarm.execution_started', [ 'msg' => 'Asynchronous production line active on background server.' ] );
        }

        try {
            do_action( 'tersostudio_process_job_pipeline_step', $job_id, $project_id );

            $this->advance_job_state( $job_id, 'completed', 100, 'none' );
            if ( $journal ) {
                $journal->record_event( $job_id, 'ts_job.finished_success', [ 'msg' => 'Production line completed tasks file-by-file.' ] );
            }
        } catch ( \Throwable $e ) {
            $this->advance_job_state( $job_id, 'failed', 0 );
            if ( $journal ) {
                $journal->record_event( $job_id, 'ts_job.failed_exception', [ 'error' => $e->getMessage() ] );
            }
            $repo = TERSOSTUDIO_Service_Container::get_instance()->make( 'project_state_repo' );
            if ( $repo ) {
                $repo->log_error( 'critical', 'Async worker loop crash exception: ' . $e->getMessage(), [ 'job_id' => $job_id, 'project_id' => $project_id ] );
            }
        }
    }
}
