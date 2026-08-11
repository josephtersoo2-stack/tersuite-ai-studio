<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Filesystem_Gate {
    private static ?TERSOSTUDIO_Filesystem_Gate $instance = null;
    private $wp_fs = null;

    private function __construct() {
        $this->initialize_wp_filesystem();
    }

    public static function get_instance(): TERSOSTUDIO_Filesystem_Gate {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initialize_wp_filesystem(): void {
        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
        $this->wp_fs = $wp_filesystem;
    }

    private function ensure_filesystem(): bool {
        if ( empty( $this->wp_fs ) ) {
            $this->initialize_wp_filesystem();
        }
        return ! empty( $this->wp_fs );
    }

    private function verify_path_security( string $path ): bool {
        require_once TERSOSTUDIO_PATH . 'core/Security/class-path-validator.php';
        return TERSOSTUDIO_Path_Validator::get_instance()->is_path_safe( $path );
    }

    public function read( string $path ): ?string {
        if ( ! $this->ensure_filesystem() || ! $this->verify_path_security( $path ) ) {
            return null;
        }
        if ( ! $this->wp_fs->exists( $path ) || $this->wp_fs->is_dir( $path ) ) {
            return null;
        }
        $contents = $this->wp_fs->get_contents( $path );
        return ( false === $contents ) ? null : $contents;
    }

    public function write( string $path, string $contents ): bool {
        if ( ! $this->ensure_filesystem() || ! $this->verify_path_security( $path ) ) {
            return false;
        }
        $directory = dirname( $path );
        if ( ! $this->wp_fs->is_dir( $directory ) ) {
            if ( ! $this->wp_fs->mkdir( $directory, FS_CHMOD_DIR ) ) {
                return false;
            }
        }
        return (bool) $this->wp_fs->put_contents( $path, $contents, FS_CHMOD_FILE );
    }

    public function mkdir( string $path ): bool {
        if ( ! $this->ensure_filesystem() || ! $this->verify_path_security( $path ) ) {
            return false;
        }
        if ( $this->wp_fs->is_dir( $path ) ) {
            return true;
        }
        return (bool) $this->wp_fs->mkdir( $path, FS_CHMOD_DIR );
    }

    public function delete( string $path ): bool {
        if ( ! $this->ensure_filesystem() || ! $this->verify_path_security( $path ) ) {
            return false;
        }
        if ( ! $this->wp_fs->exists( $path ) ) {
            return true;
        }
        return (bool) $this->wp_fs->delete( $path, true );
    }

    public function exists( string $path ): bool {
        if ( ! $this->ensure_filesystem() || ! $this->verify_path_security( $path ) ) {
            return false;
        }
        return (bool) $this->wp_fs->exists( $path );
    }

    public function is_directory( string $path ): bool {
        if ( ! $this->ensure_filesystem() || ! $this->verify_path_security( $path ) ) {
            return false;
        }
        return (bool) $this->wp_fs->is_dir( $path );
    }

    public function get_size( string $path ): int {
        if ( ! $this->ensure_filesystem() || ! $this->verify_path_security( $path ) ) {
            return 0;
        }
        return (int) $this->wp_fs->size( $path );
    }

    public function list_directory_contents( string $path ): array {
        if ( ! $this->ensure_filesystem() || ! $this->verify_path_security( $path ) || ! $this->is_directory( $path ) ) {
            return [];
        }
        $results = [];
        try {
            $dir = new DirectoryIterator( $path );
            foreach ( $dir as $item ) {
                if ( $item->isDot() ) {
                    continue;
                }
                $results[] = [
                    'name'         => $item->getFilename(),
                    'path'         => $item->getPathname(),
                    'is_directory' => $item->isDir(),
                ];
            }
        } catch ( \UnexpectedValueException $e ) {
            return [];
        }
        return $results;
    }
}
