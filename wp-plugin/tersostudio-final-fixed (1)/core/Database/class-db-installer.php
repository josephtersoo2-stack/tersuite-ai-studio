<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_DB_Installer {
    public static function scaffold_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $debug_id = 'ts_db_' . wp_generate_password( 8, false, false );
        
        $tables = [
            'ts_projects' => "CREATE TABLE {$wpdb->prefix}ts_projects (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                slug varchar(255) NOT NULL,
                category_slug varchar(64) NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug)
            ) $charset_collate;",

            'ts_workspace_files' => "CREATE TABLE {$wpdb->prefix}ts_workspace_files (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                project_id bigint(20) unsigned NOT NULL,
                file_path varchar(512) NOT NULL,
                code_buffer longtext NOT NULL,
                last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY project_id (project_id)
            ) $charset_collate;",

            'ts_jobs' => "CREATE TABLE {$wpdb->prefix}ts_jobs (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                project_id bigint(20) unsigned NOT NULL,
                status varchar(32) NOT NULL,
                progress_percentage tinyint(3) DEFAULT 0,
                active_agent varchar(64) DEFAULT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY project_id (project_id),
                KEY status (status),
                KEY created_at (created_at)
            ) $charset_collate;",

            'ts_event_journal' => "CREATE TABLE {$wpdb->prefix}ts_event_journal (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                job_id bigint(20) unsigned NOT NULL,
                event_name varchar(128) NOT NULL,
                payload_data longtext NOT NULL,
                logged_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY job_id (job_id)
            ) $charset_collate;",

            'ts_chat_history' => "CREATE TABLE {$wpdb->prefix}ts_chat_history (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                project_id bigint(20) unsigned NOT NULL,
                sender_role varchar(32) NOT NULL,
                message_body longtext NOT NULL,
                token_count int(10) unsigned DEFAULT 0,
                PRIMARY KEY (id),
                KEY project_id (project_id)
            ) $charset_collate;",

            'ts_knowledge' => "CREATE TABLE {$wpdb->prefix}ts_knowledge (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                lookup_keyword varchar(128) NOT NULL,
                context_tag varchar(64) NOT NULL,
                reference_text longtext NOT NULL,
                PRIMARY KEY (id),
                KEY context_tag (context_tag),
                FULLTEXT KEY lookup_text (lookup_keyword, reference_text)
            ) $charset_collate;",

            'ts_error_memory' => "CREATE TABLE {$wpdb->prefix}ts_error_memory (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                file_path varchar(255) NOT NULL,
                error_signature varchar(255) NOT NULL,
                failed_code longtext NOT NULL,
                corrected_code longtext NOT NULL,
                learned_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY error_signature (error_signature)
            ) $charset_collate;",

            'ts_snapshots' => "CREATE TABLE {$wpdb->prefix}ts_snapshots (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                project_id bigint(20) unsigned NOT NULL,
                snapshot_name varchar(255) NOT NULL,
                snapshot_path varchar(512) NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY project_id (project_id)
            ) $charset_collate;",

            'ts_logs' => "CREATE TABLE {$wpdb->prefix}ts_logs (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                log_level varchar(32) NOT NULL,
                message longtext NOT NULL,
                context longtext NOT NULL,
                logged_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;"
        ];

        foreach ( $tables as $table_name => $sql ) {
            dbDelta( $sql );
            if ( ! empty( $wpdb->last_error ) ) {
                return [
                    'success'  => false,
                    'message'  => 'Database structural installation failure on ' . $table_name . ': ' . $wpdb->last_error,
                    'code'     => 'db_delta_error',
                    'debug_id' => $debug_id,
                    'data'     => []
                ];
            }
        }

        return [
            'success'  => true,
            'message'  => 'Database schema optimization layout created successfully.',
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => []
        ];
    }
}
