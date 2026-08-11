<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Project_Ingestion_Service {
    private static ?TERSOSTUDIO_Project_Ingestion_Service $instance = null;

    private function __construct() {}

    public static function get_instance(): TERSOSTUDIO_Project_Ingestion_Service {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function process_local_directory_ingestion( int $project_id, string $source_plugin, string $sandbox_path, string $slug ): void {
        $container = TERSOSTUDIO_Service_Container::get_instance();
        $repo = $container->make( 'project_state_repo' );
        $fs   = $container->make( 'filesystem_gate' );

        $plugin_base_dir = trailingslashit( WP_PLUGIN_DIR ) . $source_plugin;
        if ( $fs && $fs->is_directory( $plugin_base_dir ) ) {
            $this->ingest_directory_recursive( $plugin_base_dir, $sandbox_path, '', $project_id, $repo, $fs );
            
            $scanned_file_paths = $this->collect_files_recursive( $sandbox_path, $fs );

            require_once TERSOSTUDIO_PATH . 'core/RAG/class-codebase-analyzer.php';
            $analyzer = TERSOSTUDIO_Codebase_Analyzer::get_instance();
            $this->run_codebase_analysis( $analyzer, $scanned_file_paths, $slug );
        }
    }

    public function process_zip_package_ingestion( int $project_id, array $zip_file, string $sandbox_path, string $slug ): bool {
        $container = TERSOSTUDIO_Service_Container::get_instance();
        $repo = $container->make( 'project_state_repo' );
        $fs   = $container->make( 'filesystem_gate' );

        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();

        $temp_staging = trailingslashit( wp_upload_dir()['basedir'] ) . 'tersostudio/temp/staging_' . time() . '/';
        if ( $fs ) {
            $fs->mkdir( $temp_staging );
        }

        $unzipped = unzip_file( $zip_file['tmp_name'], $temp_staging );
        if ( is_wp_error( $unzipped ) ) {
            if ( $fs ) {
                $fs->delete( $temp_staging );
            }
            return false;
        }

        $this->process_unzipped_ingestion_recursive( $temp_staging, $sandbox_path, '', $project_id, $repo, $fs );

        require_once TERSOSTUDIO_PATH . 'core/RAG/class-codebase-analyzer.php';
        $analyzer = TERSOSTUDIO_Codebase_Analyzer::get_instance();

        $scanned_file_paths = $this->collect_files_recursive( $sandbox_path, $fs );
        $this->run_codebase_analysis( $analyzer, $scanned_file_paths, $slug );
        
        if ( $fs ) {
            $fs->delete( $temp_staging );
        }
        return true;
    }

    private function run_codebase_analysis( $analyzer, array $scanned_file_paths, string $slug ): void {
        $container = TERSOSTUDIO_Service_Container::get_instance();
        $footprint = $analyzer->map_plugin_architecture_footprint( $scanned_file_paths );
        $rag = $container->make( 'rag_orchestrator' );
        
        if ( $rag && ! empty( $footprint ) ) {
            foreach ( $footprint as $vector_key => $items_list ) {
                if ( empty( $items_list ) ) continue;
                $guide_summary = "The uploaded workspace archive plugin '{$slug}' exposes these structural components matching model pattern [{$vector_key}]: " . implode( ', ', $items_list );
                $rag->inject_knowledge_base_record( $slug . ' ' . $vector_key . ' code markers', 'wp_core', $guide_summary );
            }
        }
    }

    private function collect_files_recursive( string $dir, $fs ): array {
        if ( ! $fs ) return [];
        $files = [];
        $items = $fs->list_directory_contents( $dir );
        foreach ( $items as $item ) {
            if ( $item['is_directory'] ) {
                $files = array_merge( $files, $this->collect_files_recursive( $item['path'], $fs ) );
            } else {
                $files[] = $item['path'];
            }
        }
        return $files;
    }

    private function ingest_directory_recursive( string $source, string $target_base, string $relative_path, int $project_id, $repo, $fs ): void {
        if ( ! $fs ) return;
        $items = $fs->list_directory_contents( $source );
        
        $ignored_directories = [ 'vendor', 'node_modules', 'tests', 'build', '.git', 'assets', 'images', 'languages' ];
        $blocked_patterns    = [ '.min.js', '.map', 'bundle', 'dist' ];
        $allowed_extensions  = [ 'php', 'js', 'json' ];

        foreach ( $items as $item ) {
            $file = $item['name'];
            $src_path = $item['path'];
            $rel_sub_path = empty( $relative_path ) ? $file : $relative_path . '/' . $file;

            if ( $item['is_directory'] ) {
                if ( in_array( $file, $ignored_directories, true ) ) continue;
                $fs->mkdir( $target_base . $rel_sub_path );
                $this->ingest_directory_recursive( $src_path, $target_base, $rel_sub_path, $project_id, $repo, $fs );
            } else {
                $ext = strtolower( pathinfo( $src_path, PATHINFO_EXTENSION ) );
                if ( ! in_array( $ext, $allowed_extensions, true ) ) continue;

                $should_block = false;
                foreach ( $blocked_patterns as $pattern ) {
                    if ( str_contains( strtolower( $file ), $pattern ) ) {
                        $should_block = true;
                        break;
                    }
                }
                if ( $should_block ) continue;

                if ( $fs->get_size( $src_path ) > 1024 * 1024 ) {
                    continue;
                }

                $content = $fs->read( $src_path );
                if ( null !== $content ) {
                    $fs->write( $target_base . $rel_sub_path, $content );
                    if ( $repo ) $repo->save_workspace_file( $project_id, $rel_sub_path, $content );
                }
            }
        }
    }

    private function process_unzipped_ingestion_recursive( string $source, string $target_base, string $relative_path, int $project_id, $repo, $fs ): void {
        $this->ingest_directory_recursive( $source, $target_base, $relative_path, $project_id, $repo, $fs );
    }
}
