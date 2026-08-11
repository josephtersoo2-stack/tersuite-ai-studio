<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Event_Journal {
    private static ?TERSOSTUDIO_Event_Journal $instance = null;

    private function __construct() {}

    public static function get_instance(): TERSOSTUDIO_Event_Journal {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function record_event( int $job_id, string $event_name, array $payload_data = [] ): array {
        global $wpdb;
        $debug_id = 'ts_evt_' . wp_generate_password( 8, false, false );
        $table = $wpdb->prefix . 'ts_event_journal';

        $result = $wpdb->insert(
            $table,
            [
                'job_id'       => $job_id,
                'event_name'   => sanitize_text_field( $event_name ),
                'payload_data' => wp_json_encode( $payload_data ),
                'logged_at'    => current_time( 'mysql' )
            ],
            [ '%d', '%s', '%s', '%s' ]
        );

        if ( false === $result ) {
            return [
                'success'  => false,
                'message'  => 'Database execution logging failure inside the persistent event bus ledger table: ' . $wpdb->last_error,
                'code'     => 'event_logging_failure',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        return [
            'success'  => true,
            'message'  => 'Asynchronous transaction milestone logged completely to table ledger.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => []
        ];
    }

    public function fetch_job_history( int $job_id ): array {
        global $wpdb;
        $debug_id = 'ts_evt_' . wp_generate_password( 8, false, false );
        $table = $wpdb->prefix . 'ts_event_journal';

        $query = $wpdb->prepare( "SELECT event_name, payload_data, logged_at FROM {$table} WHERE job_id = %d ORDER BY id ASC", $job_id );
        $results = $wpdb->get_results( $query, ARRAY_A );

        $history = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $row ) {
                $history[] = [
                    'event'     => $row['event_name'],
                    'timestamp' => $row['logged_at'],
                    'payload'   => json_decode( $row['payload_data'], true ) ?: []
                ];
            }
        }

        return [
            'success'  => true,
            'message'  => 'Persistent transaction history journals retrieved from data records.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => [ 'history' => $history ]
        ];
    }
}
