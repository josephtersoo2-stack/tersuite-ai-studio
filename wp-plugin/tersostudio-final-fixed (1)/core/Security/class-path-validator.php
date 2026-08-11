<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Path_Validator {
    private static ?TERSOSTUDIO_Path_Validator $instance = null;
    private string $allowed_root;
    private string $plugin_root;

    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->allowed_root = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . 'tersostudio' );
        $this->plugin_root  = wp_normalize_path( TERSOSTUDIO_PATH );
    }

    public static function get_instance(): TERSOSTUDIO_Path_Validator {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Evaluates a requested filesystem path against directory containment boundaries and explicit admin locks.
     * 
     * @param string $requested_path The path to check.
     * @return bool True if the path is completely sandboxed and safe; false otherwise.
     */
    public function is_path_safe( string $requested_path ): bool {
        if ( empty( $requested_path ) ) {
            return false;
        }

        $normalized_path = wp_normalize_path( $requested_path );

        // 1. Prevent primitive directory climbing tricks
        if ( str_contains( $normalized_path, '../' ) || str_contains( $normalized_path, '/..' ) ) {
            return false;
        }

        // 2. Dual-Root Allocation Check: Authorize path if it falls under sandbox uploads OR internal system files
        $is_in_sandbox = str_starts_with( $normalized_path, $this->allowed_root );
        $is_in_plugin  = str_starts_with( $normalized_path, $this->plugin_root );

        if ( ! $is_in_sandbox && ! $is_in_plugin ) {
            return false;
        }

        // 3. Query the Active Security Sentinel database rules for hardlocked No-Go Zones
        $nogo_zones_option = (string) get_option( 'tersostudio_security_nogo_zones', "wp-config.php\n.env\nwp-admin\nwp-includes" );
        $nogo_zones = explode( "\n", str_replace( "\r", "", $nogo_zones_option ) );

        foreach ( $nogo_zones as $zone ) {
            $cleaned_zone = trim( $zone );
            if ( ! empty( $cleaned_zone ) && str_contains( strtolower( $normalized_path ), strtolower( $cleaned_zone ) ) ) {
                return false;
            }
        }

        return true;
    }
}
