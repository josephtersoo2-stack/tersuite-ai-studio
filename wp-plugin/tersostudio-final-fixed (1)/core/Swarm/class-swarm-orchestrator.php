<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Swarm_Orchestrator {
    private static ?TERSOSTUDIO_Swarm_Orchestrator $instance = null;

    private function __construct() {
        add_action( 'tersostudio_process_job_pipeline_step', [ $this, 'execute_swarm_production_line' ], 10, 2 );
    }

    public static function get_instance(): TERSOSTUDIO_Swarm_Orchestrator {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function execute_swarm_production_line( int $job_id, int $project_id ): void {
        global $wpdb;
        $container = TERSOSTUDIO_Service_Container::get_instance();
        
        $journal  = $container->make( 'event_journal' );
        $queue    = $container->make( 'job_orchestrator' );
        $repo     = $container->make( 'project_state_repo' );
        $learning = $container->make( 'learning_engine' );

        require_once TERSOSTUDIO_PATH . 'core/Swarm/Agents/class-agent-spec-architect.php';
        require_once TERSOSTUDIO_PATH . 'core/Swarm/Agents/class-agent-core-developer.php';
        require_once TERSOSTUDIO_PATH . 'core/Swarm/Agents/class-agent-safety-validator.php';

        $architect = new TERSOSTUDIO_Agent_Spec_Architect();
        $developer = new TERSOSTUDIO_Agent_Core_Developer();
        $validator = new TERSOSTUDIO_Agent_Safety_Validator();

        $job_agent_assignment = $wpdb->get_var( $wpdb->prepare( "SELECT active_agent FROM {$wpdb->prefix}ts_jobs WHERE id = %d", $job_id ) );
        $chat_table = $wpdb->prefix . 'ts_chat_history';

        // STAGE 1 ROUTINE: Conversational Planning & Specification Extraction Pass
        if ( 'architect' === $job_agent_assignment ) {
            $queue->advance_job_state( $job_id, 'running', 40, 'architect' );

            // Chronological Context Assembly: Collect the entire conversation timeline loop history to feed agent short-term memory maps
            $history_rows = $wpdb->get_results( $wpdb->prepare( "SELECT sender_role, message_body FROM {$chat_table} WHERE project_id = %d ORDER BY id ASC", $project_id ), ARRAY_A );
            
            $spec_result = $architect->generate_specification( $history_rows ?: [] );
            
            if ( ! $spec_result['success'] ) {
                throw new \Exception( 'Architect Operational Exception: ' . $spec_result['message'] );
            }

            $response_matrix = $spec_result['data']['response'];
            $is_blueprint_ready = ! empty( $response_matrix['is_blueprint_ready'] );
            $chat_prose_output = $response_matrix['chat_response'] ?? 'I have updated our design tracking notes.';

            // Commit the Architect's response text dynamically to your persistent UI chat dashboard timeline
            $wpdb->insert( $chat_table, [
                'project_id'   => $project_id,
                'sender_role'  => 'agent_liaison',
                'message_body' => $chat_prose_output,
                'token_count'  => 0
            ], [ '%d', '%s', '%s', '%d' ] );

            // If the co-planning phase isn't complete, log the conversation and exit the background worker task loop cleanly
            if ( ! $is_blueprint_ready ) {
                $queue->advance_job_state( $job_id, 'completed', 100, 'architect' );
                if ( $journal ) {
                    $journal->record_event( $job_id, 'ts_spec_planner.chat_processed', [ 'msg' => 'Planning context updated and synchronized successfully.' ] );
                }
                return;
            }

            // Shaking Hands on Design State: Freeze the structural design arrays into project directory workspace files maps
            $blueprint = $response_matrix['blueprint'] ?? [];
            $blueprint_string_content = wp_json_encode( $blueprint, JSON_PRETTY_PRINT );
            
            $repo->save_workspace_file( $project_id, 'tersostudio-blueprint.json', $blueprint_string_content );
            $fs = $container->make( 'filesystem_gate' );
            if ( $fs ) {
                $upload_dir = wp_upload_dir();
                $fs->write( trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/workspace/project-' . $project_id . '/tersostudio-blueprint.json', $blueprint_string_content );
            }

            if ( $journal ) {
                $journal->record_event( $job_id, 'ts_spec_planner.completed', [ 'msg' => 'Structural technical layout contract committed to workspace file explorer trees.' ] );
            }
            return;
        }

        // STAGE 2 ROUTINE: Interactive Confirmed Multi-Pass Build Cycle Pass
        if ( 'developer' === $job_agent_assignment ) {
            $bp_raw_buffer = $wpdb->get_var( $wpdb->prepare( "SELECT code_buffer FROM {$wpdb->prefix}ts_workspace_files WHERE project_id = %d AND file_path = 'tersostudio-blueprint.json'", $project_id ) );
            if ( empty( $bp_raw_buffer ) ) {
                throw new \Exception( 'Execution aborted: No compiled architectural plan blueprint configuration file detected for this project workspace context.' );
            }

            $blueprint = json_decode( $bp_raw_buffer, true ) ?: [];
            $files_to_build = $blueprint['files'] ?? [];
            if ( empty( $files_to_build ) ) {
                throw new \Exception( 'Specification Parse Error: Master blueprint layout contains zero target compilation file descriptors.' );
            }

            $total_files = count( $files_to_build );
            $current_index = 0;
            $upload_dir = wp_upload_dir();
            $sandbox_base = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/workspace/project-' . $project_id . '/';

            foreach ( $files_to_build as $file_meta ) {
                $file_path = sanitize_text_field( $file_meta['path'] ?? '' );
                if ( empty( $file_path ) || 'tersostudio-blueprint.json' === $file_path ) continue;

                $current_index++;
                $progress = 20 + intval( ( $current_index / $total_files ) * 80 );
                $max_healing_passes = 3;
                $passed_audit = false;
                $last_validation_errors = '';

                for ( $pass = 1; $pass <= $max_healing_passes; $pass++ ) {
                    $queue->advance_job_state( $job_id, 'running', $progress, 'developer' );
                    if ( $journal ) {
                        $journal->record_event( $job_id, 'ts_core_dev.started', [ 'file' => $file_path, 'msg' => ( $pass === 1 ) ? 'Assembling source code text fields.' : "Self-healing pass execution loop #{$pass} active." ] );
                    }

                    $dev_result = $developer->build_file_code( $file_path, $blueprint );
                    if ( ! $dev_result['success'] ) throw new \Exception( "Generation Crash on {$file_path}: " . $dev_result['message'] );

                    $generated_code = $dev_result['data']['content'];
                    $val_result = $validator->audit_code_compliance( $file_path, $generated_code );
                    
                    if ( ! empty( $val_result['data']['passed'] ) ) {
                        $passed_audit = true;
                        break;
                    }

                    $issues_list = $val_result['data']['issues'] ?? [ 'Unknown syntax validation drop.' ];
                    $last_validation_errors = implode( ' | ', $issues_list );
                    
                    if ( $journal ) {
                        $journal->record_event( $job_id, 'ts_qa_validation.failed', [ 'file' => $file_path, 'error' => "Pass #{$pass} Rejected: " . $last_validation_errors ] );
                    }
                    if ( $learning ) {
                        $learning->archive_healing_milestone( $file_path, md5( $file_path ), $generated_code, "CRITICAL WPCS FIX REQUIREMENT: " . $last_validation_errors );
                    }
                    sleep(1);
                }

                if ( ! $passed_audit ) {
                    throw new \Exception( sprintf( 'Security Safeguard Blocked Build on %s after maximum healing passes: %s', $file_path, $last_validation_errors ) );
                }

                if ( $repo ) $repo->save_workspace_file( $project_id, $file_path, $generated_code );
                $fs = $container->make( 'filesystem_gate' );
                if ( $fs ) {
                    $absolute_disk_target = $sandbox_base . ltrim( $file_path, '/' );
                    wp_mkdir_p( dirname( $absolute_disk_target ) );
                    $fs->write( $absolute_disk_target, $generated_code );
                }
                if ( $journal ) {
                    $journal->record_event( $job_id, 'ts_core_dev.completed', [ 'file' => $file_path, 'msg' => 'Component verified and locked to workspace directory arrays.' ] );
                }
            }
        }
    }
}
