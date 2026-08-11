<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Restore_Point_Manager {
    private static ?TERSOSTUDIO_Restore_Point_Manager $instance = null;

    private function __construct() {}

    public static function get_instance(): TERSOSTUDIO_Restore_Point_Manager {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function create_git_snapshot( int $project_id, string $snapshot_name ): array {
        global $wpdb;
        $debug_id = 'ts_rst_' . wp_generate_password( 8, false, false );
        $table = $wpdb->prefix . 'ts_snapshots';

        $container = TERSOSTUDIO_Service_Container::get_instance();
        $repo = $container->make( 'project_state_repo' );
        $fs   = $container->make( 'filesystem_gate' );

        $files_res = $repo->get_workspace_files( $project_id );
        $files = $files_res['data']['files'] ?? [];

        if ( empty( $files ) ) {
            return [
                'success'  => false,
                'message'  => 'Archival aborted: Active workspace contains zero code files to index.',
                'code'     => 'empty_workspace',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        $snapshot_slug = 'snap_' . time() . '_' . wp_generate_password( 4, false, false );
        $upload_dir = wp_upload_dir();
        $snapshot_dest_path = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/snapshots/project-' . $project_id . '/' . $snapshot_slug . '/';

        if ( $fs ) {
            $fs->mkdir( $snapshot_dest_path );
        }

        foreach ( $files as $path => $code ) {
            $absolute_target_file = $snapshot_dest_path . ltrim( $path, '/' );
            $target_directory_parent = dirname( $absolute_target_file );
            
            if ( $fs ) {
                $fs->mkdir( $target_directory_parent );
                $fs->write( $absolute_target_file, $code );
            }
        }

        $wpdb->insert(
            $table,
            [
                'project_id'    => $project_id,
                'snapshot_name' => sanitize_text_field( $snapshot_name ),
                'snapshot_path' => sanitize_text_field( $snapshot_slug ),
                'created_at'    => current_time( 'mysql' )
            ],
            [ '%d', '%s', '%s', '%s' ]
        );

        $this->execute_automatic_policy_prune( $project_id, $snapshot_dest_path, $fs );

        return [
            'success'  => true,
            'message'  => 'Workspace snapshot successfully frozen and committed to archival memory lines.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => []
        ];
    }

    public function delete_git_snapshot( int $project_id, string $snapshot_slug ): array {
        global $wpdb;
        $debug_id = 'ts_rst_' . wp_generate_password( 8, false, false );
        $table = $wpdb->prefix . 'ts_snapshots';
        $fs = TERSOSTUDIO_Service_Container::get_instance()->make( 'filesystem_gate' );

        $upload_dir = wp_upload_dir();
        $snapshot_path = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/snapshots/project-' . $project_id . '/' . $snapshot_slug . '/';

        if ( $fs && $fs->is_directory( $snapshot_path ) ) {
            $fs->delete( $snapshot_path );
        }

        $wpdb->delete( $table, [ 'project_id' => $project_id, 'snapshot_path' => $snapshot_slug ], [ '%d', '%s' ] );

        return [
            'success'  => true,
            'message'  => 'Snapshot removed and deleted permanently from local storage disks.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => []
        ];
    }

    public function restore_git_snapshot( int $project_id, string $snapshot_slug ): array {
        global $wpdb;
        $debug_id = 'ts_rst_' . wp_generate_password( 8, false, false );

        $container = TERSOSTUDIO_Service_Container::get_instance();
        $repo = $container->make( 'project_state_repo' );
        $fs   = $container->make( 'filesystem_gate' );

        $upload_dir = wp_upload_dir();
        $snapshot_src_path = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/snapshots/project-' . $project_id . '/' . $snapshot_slug . '/';
        $sandbox_base = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/workspace/project-' . $project_id . '/';

        if ( ! $fs || ! $fs->is_directory( $snapshot_src_path ) ) {
            return [
                'success'  => false,
                'message'  => 'Rollback execution aborted: Historical snapshot source directory not found on storage disk.',
                'code'     => 'snapshot_missing',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        $fs->delete( $sandbox_base );
        $fs->mkdir( $sandbox_base );

        $files_table = $wpdb->prefix . 'ts_workspace_files';
        $wpdb->delete( $files_table, [ 'project_id' => $project_id ], [ '%d' ] );

        $this->restore_directory_recursive( $snapshot_src_path, $sandbox_base, '', $project_id, $repo, $fs );

        return [
            'success'  => true,
            'message'  => 'Workspace layout successfully rolled back to target timeline state context.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => []
        ];
    }

    private function execute_automatic_policy_prune( int $project_id, string $dest_base, $fs ): void {
        global $wpdb;
        $table = $wpdb->prefix . 'ts_snapshots';

        $max_allowed  = intval( get_option( 'tersostudio_policy_max_snapshots', 20 ) );
        $expiry_days  = intval( get_option( 'tersostudio_policy_expiry_days', 30 ) );
        $upload_dir   = wp_upload_dir();
        $project_root = trailingslashit( $upload_dir['basedir'] ) . 'tersostudio/snapshots/project-' . $project_id . '/';

        if ( $expiry_days > 0 ) {
            $threshold_date = date( 'Y-m-d H:i:s', time() - ( $expiry_days * 86400 ) );
            $expired_query  = $wpdb->prepare( "SELECT snapshot_path FROM {$table} WHERE project_id = %d AND created_at < %s", $project_id, $threshold_date );
            $expired_snaps  = $wpdb->get_col( $expired_query );
            
            if ( is_array( $expired_snaps ) && $fs ) {
                foreach ( $expired_snaps as $slug ) {
                    if ( ! empty( $slug ) ) {
                        $fs->delete( $project_root . $slug . '/' );
                        $wpdb->delete( $table, [ 'project_id' => $project_id, 'snapshot_path' => $slug ], [ '%d', '%s' ] );
                    }
                }
            }
        }

        if ( $max_allowed > 0 ) {
            $active_query = $wpdb->prepare( "SELECT id, snapshot_path FROM {$table} WHERE project_id = %d ORDER BY id DESC", $project_id );
            $active_snaps = $wpdb->get_results( $active_query, ARRAY_A );

            if ( is_array( $active_snaps ) && count( $active_snaps ) > $max_allowed && $fs ) {
                $excess_records = array_slice( $active_snaps, $max_allowed );
                foreach ( $excess_records as $excess ) {
                    $slug = $excess['snapshot_path'];
                    if ( ! empty( $slug ) ) {
                        $fs->delete( $project_root . $slug . '/' );
                        $wpdb->delete( $table, [ 'id' => intval( $excess['id'] ) ], [ '%d' ] );
                    }
                }
            }
        }
    }

    private function restore_directory_recursive( string $source, string $target_base, string $relative_path, int $project_id, $repo, $fs ): void {
        if ( ! $fs || ! $fs->is_directory( $source ) ) {
            return;
        }

        try {
            $directory = new DirectoryIterator( $source );
        } catch ( \UnexpectedValueException $e ) {
            return;
        }

        foreach ( $directory as $item ) {
            if ( $item->isDot() ) {
                continue;
            }

            $src_path = $item->getPathname();
            $file = $item->getFilename();
            $rel_sub_path = empty( $relative_path ) ? $file : $relative_path . '/' . $file;

            if ( $item->isDir() ) {
                $fs->mkdir( $target_base . $rel_sub_path );
                $this->restore_directory_recursive( $src_path, $target_base, $rel_sub_path, $project_id, $repo, $fs );
            } else {
                $content = $fs->read( $src_path );
                if ( null !== $content ) {
                    $target_file_path = $target_base . $rel_sub_path;
                    $target_dir_parent = dirname( $target_file_path );

                    $fs->mkdir( $target_dir_parent );
                    $fs->write( $target_file_path, $content );

                    if ( $repo ) {
                        $repo->save_workspace_file( $project_id, $rel_sub_path, $content );
                    }
                }
            }
        }
    }
}
