<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Learning_Engine {
    private static ?TERSOSTUDIO_Learning_Engine $instance = null;
    private string $memory_table;

    private function __construct() {
        global $wpdb;
        $this->memory_table = $wpdb->prefix . 'ts_error_memory';
    }

    public static function get_instance(): TERSOSTUDIO_Learning_Engine {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Archives self-healing code milestones to both relational database records and permanent filesystem assets.
     */
    public function archive_healing_milestone( string $file_path, string $error_sig, string $failed_block, string $corrected_block ): bool {
        global $wpdb;

        // 1. Commit the tracking indexes to database records
        $db_result = $wpdb->insert(
            $this->memory_table,
            [
                'file_path'       => sanitize_text_field( $file_path ),
                'error_signature' => sanitize_text_field( $error_sig ),
                'failed_code'     => $failed_block,
                'corrected_code'  => $corrected_block,
                'learned_at'      => current_time( 'mysql' )
            ],
            [ '%s', '%s', '%s', '%s', '%s' ]
        );

        // 2. De-couple from active volatile workspace variables by writing an immutable JSON memory file to disk
        $container = TERSOSTUDIO_Service_Container::get_instance();
        $fs = $container->make( 'filesystem_gate' );

        if ( $fs ) {
            $upload_dir = wp_upload_dir();
            $memory_root_path = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/memory/';
            
            $memory_payload = [
                'error_signature' => $error_sig,
                'target_file'     => $file_path,
                'captured_at'     => current_time( 'mysql' ),
                'diagnostics'     => [
                    'broken_source_buffer'  => $failed_block,
                    'remediated_normalized' => $corrected_block
                ]
            ];

            // Write file-by-file using distinct hashed naming maps to prevent file description collisions
            $absolute_memory_file_target = $memory_root_path . 'sig_' . sanitize_key( $error_sig ) . '.json';
            $fs->write( $absolute_memory_file_target, wp_json_encode( $memory_payload, JSON_PRETTY_PRINT ) );
        }

        return false !== $db_result;
    }

    /**
     * Queries the learning subsystems, prioritizing ultra-fast local disk memory maps before querying relational database fallback lines.
     */
    public function query_learned_memory( string $error_sig ): array {
        global $wpdb;

        $container = TERSOSTUDIO_Service_Container::get_instance();
        $fs = $container->make( 'filesystem_gate' );

        if ( $fs ) {
            $upload_dir = wp_upload_dir();
            $target_disk_memory_file = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/memory/sig_' . sanitize_key( $error_sig ) . '.json';

            if ( $fs->exists( $target_disk_memory_file ) ) {
                $raw_file_buffer = $fs->read( $target_disk_memory_file );
                if ( ! empty( $raw_file_buffer ) ) {
                    $decoded_payload = json_decode( $raw_file_buffer, true );
                    if ( ! empty( $decoded_payload['diagnostics'] ) ) {
                        return [ [
                            'failed_code'    => $decoded_payload['diagnostics']['broken_source_buffer'] ?? '',
                            'corrected_code' => $decoded_payload['diagnostics']['remediated_normalized'] ?? ''
                        ] ];
                    }
                }
            }
        }

        // Relational Fallback Pass: Reads directly from database logs if disk caches are clearing operations
        $query = $wpdb->prepare( "SELECT failed_code, corrected_code FROM {$this->memory_table} WHERE error_signature = %s ORDER BY id DESC LIMIT 2", $error_sig );
        $results = $wpdb->get_results( $query, ARRAY_A );

        return is_array( $results ) ? $results : [];
    }
}
