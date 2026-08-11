<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Codebase_Analyzer {
    private static ?TERSOSTUDIO_Codebase_Analyzer $instance = null;

    private function __construct() {}

    public static function get_instance(): TERSOSTUDIO_Codebase_Analyzer {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function map_plugin_architecture_footprint( array $file_paths ): array {
        $analysis = [
            'actions' => [], 'filters' => [], 'rest_routes' => [],
            'security' => [], 'patterns' => [], 'blueprints' => []
        ];

        $container = TERSOSTUDIO_Service_Container::get_instance();
        $fs = $container->make( 'filesystem_gate' );
        if ( ! $fs ) {
            return $analysis;
        }

        $hooks_regex = base64_decode('Lyhkb19hY3Rpb258YXBwbHlfZmlsdGVycylccypcKFxzKlsnIl0oW14nIl0rKVsnIl0v');
        $rest_regex  = base64_decode('L3JlZ2lzdGVyX3Jlc3Rfcm91dGVccypcKFxzKlsnIl0oW14nIl0rKVsnIl1ccyosXHMqWyciXShbXiciXSspWyciXS8=');

        $security_markers = [
            'wp_verify_nonce' => 'Nonce Verification', 'check_ajax_referer' => 'AJAX Nonce Check',
            'current_user_can' => 'Capability Check', 'sanitize_text_field' => 'Input Sanitization',
            'esc_html' => 'Output Escaping', '$wpdb->prepare' => 'SQL Preparation'
        ];

        $pattern_signatures = [
            'wp_ajax_' => 'AJAX Handler', 'add_shortcode' => 'Shortcode Registration',
            'wp_schedule_event' => 'Cron Job', 'register_post_type' => 'Custom Post Type'
        ];

        $blueprint_architectures = [
            'WC_Payment_Gateway' => 'WooCommerce Payment Gateway', 'WP_List_Table' => 'Data Table Architecture',
            'WP_REST_Controller' => 'REST API Controller', 'add_menu_page' => 'Admin Menu Architecture'
        ];

        foreach ( $file_paths as $file ) {
            if ( pathinfo( $file, PATHINFO_EXTENSION ) !== 'php' || ! $fs->exists( $file ) ) {
                continue;
            }

            $content = $fs->read( $file );
            if ( null === $content || '' === $content ) {
                continue;
            }

            $basename = basename( $file );

            if ( preg_match_all( $hooks_regex, $content, $matches, PREG_SET_ORDER ) ) {
                foreach ( $matches as $match ) {
                    $type = $match[1];
                    $name = trim( $match[2] );
                    if ( str_contains( $name, '$' ) || strlen( $name ) < 4 ) {
                        continue;
                    }

                    if ( 'do_action' === $type ) {
                        $analysis['actions'][] = $name . ' (file: ' . $basename . ')';
                    }
                    if ( 'apply_filters' === $type ) {
                        $analysis['filters'][] = $name . ' (file: ' . $basename . ')';
                    }
                }
            }

            if ( preg_match_all( $rest_regex, $content, $matches, PREG_SET_ORDER ) ) {
                foreach ( $matches as $match ) {
                    $analysis['rest_routes'][] = sanitize_text_field( $match[1] . ' ' . $match[2] ) . ' (file: ' . $basename . ')';
                }
            }

            foreach ( $security_markers as $sig => $label ) {
                if ( str_contains( $content, $sig ) ) {
                    $analysis['security'][] = $label . ' verified in ' . $basename;
                }
            }
            foreach ( $pattern_signatures as $sig => $label ) {
                if ( str_contains( $content, $sig ) ) {
                    $analysis['patterns'][] = $label . ' registered in ' . $basename;
                }
            }
            foreach ( $blueprint_architectures as $sig => $label ) {
                if ( str_contains( $content, $sig ) ) {
                    $analysis['blueprints'][] = $label . ' pattern deployed in ' . $basename;
                }
            }
        }

        foreach ( $analysis as $key => $val ) {
            $analysis[ $key ] = array_values( array_unique( $val ) );
        }

        return $analysis;
    }
}
