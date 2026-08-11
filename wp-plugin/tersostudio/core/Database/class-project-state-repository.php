<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Project_State_Repository {
    private static ?TERSOSTUDIO_Project_State_Repository $instance = null;

    private function __construct() {}

    public static function get_instance(): TERSOSTUDIO_Project_State_Repository {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function create_project( string $name, string $slug, string $category_slug ): array {
        global $wpdb;
        $debug_id = 'ts_repo_' . wp_generate_password( 8, false, false );

        if ( empty( trim( $name ) ) || empty( trim( $slug ) ) ) {
            return [
                'success'  => false,
                'message'  => 'Project parameters name and slug criteria cannot be blank.',
                'code'     => 'invalid_parameters',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        $table = $wpdb->prefix . 'ts_projects';
        $result = $wpdb->insert(
            $table,
            [
                'name'          => sanitize_text_field( $name ),
                'slug'          => sanitize_key( $slug ),
                'category_slug' => sanitize_key( $category_slug ),
                'created_at'    => current_time( 'mysql' )
            ],
            [ '%s', '%s', '%s', '%s' ]
        );

        if ( false === $result ) {
            $this->log_error( 'error', 'Failed project entry insertion: ' . $wpdb->last_error, [ 'slug' => $slug ] );
            return [
                'success'  => false,
                'message'  => 'Database execution write failure on project insertion: ' . $wpdb->last_error,
                'code'     => 'db_insert_failure',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        return [
            'success'  => true,
            'message'  => 'Project node registered successfully.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => [ 'project_id' => $wpdb->insert_id ]
        ];
    }

    public function get_project( int $project_id ): array {
        global $wpdb;
        $debug_id = 'ts_repo_' . wp_generate_password( 8, false, false );

        $table = $wpdb->prefix . 'ts_projects';
        $query = $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $project_id );
        $project = $wpdb->get_row( $query, ARRAY_A );

        if ( empty( $project ) ) {
            return [
                'success'  => false,
                'message'  => 'Target instance scope identifier context records not found.',
                'code'     => 'project_not_found',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        return [
            'success'  => true,
            'message'  => 'Project hydration structure retrieved successfully.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => [ 'project' => $project ]
        ];
    }

    public function save_workspace_file( int $project_id, string $file_path, string $code_buffer ): array {
        global $wpdb;
        $debug_id = 'ts_repo_' . wp_generate_password( 8, false, false );
        $table = $wpdb->prefix . 'ts_workspace_files';

        $query = $wpdb->prepare( "SELECT id FROM {$table} WHERE project_id = %d AND file_path = %s", $project_id, $file_path );
        $existing_id = $wpdb->get_var( $query );

        if ( ! empty( $existing_id ) ) {
            $result = $wpdb->update(
                $table,
                [ 'code_buffer' => $code_buffer, 'last_modified' => current_time( 'mysql' ) ],
                [ 'id' => $existing_id ],
                [ '%s', '%s' ],
                [ '%d' ]
            );
        } else {
            $result = $wpdb->insert(
                $table,
                [
                    'project_id'    => $project_id,
                    'file_path'     => $file_path,
                    'code_buffer'   => $code_buffer,
                    'last_modified' => current_time( 'mysql' )
                ],
                [ '%d', '%s', '%s', '%s' ]
            );
        }

        if ( false === $result ) {
            $this->log_error( 'error', 'Failed file persistence block operation: ' . $wpdb->last_error, [ 'project_id' => $project_id, 'file_path' => $file_path ] );
            return [
                'success'  => false,
                'message'  => 'Database write execution break on virtual text persistence stream.',
                'code'     => 'db_write_failure',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        return [
            'success'  => true,
            'message'  => 'Workspace layout code context synchronized successfully.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => []
        ];
    }

    public function get_workspace_files( int $project_id ): array {
        global $wpdb;
        $debug_id = 'ts_repo_' . wp_generate_password( 8, false, false );
        $table = $wpdb->prefix . 'ts_workspace_files';

        $query = $wpdb->prepare( "SELECT file_path, code_buffer FROM {$table} WHERE project_id = %d", $project_id );
        $results = $wpdb->get_results( $query, ARRAY_A );

        $files = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $row ) {
                $files[ $row['file_path'] ] = $row['code_buffer'];
            }
        }

        return [
            'success'  => true,
            'message'  => 'Virtual codebase file catalog populated.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => [ 'files' => $files ]
        ];
    }

    public function log_error( string $level, string $message, array $context = [] ): void {
        global $wpdb;
        $table = $wpdb->prefix . 'ts_logs';
        @$wpdb->insert(
            $table,
            [
                'log_level'  => sanitize_text_field( $level ),
                'message'    => sanitize_textarea_field( $message ),
                'context'    => wp_json_encode( $context ),
                'logged_at'  => current_time( 'mysql' )
            ],
            [ '%s', '%s', '%s', '%s' ]
        );
    }
}
