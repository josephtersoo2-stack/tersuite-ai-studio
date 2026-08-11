<?php
defined('ABSPATH') || exit;

class Tersuite_AI_Asset_Manager {
    public function register() {
        wp_register_style('tersuite-ai-admin', TERSUITE_AI_URL . 'assets/css/admin.css', array(), TERSUITE_AI_VERSION);
        wp_register_style('tersuite-ai-studio', TERSUITE_AI_URL . 'assets/css/ai-studio.css', array('tersuite-ai-admin'), TERSUITE_AI_VERSION);

        // Core utilities & API wrapper
        wp_register_script('tersuite-ai-app', TERSUITE_AI_URL . 'assets/js/app.js', array('jquery'), TERSUITE_AI_VERSION, true);
        wp_register_script('tersuite-ai-api', TERSUITE_AI_URL . 'assets/js/api.js', array('jquery', 'tersuite-ai-app'), TERSUITE_AI_VERSION, true);
        wp_register_script('tersuite-ai-websocket', TERSUITE_AI_URL . 'assets/js/websocket.js', array('jquery', 'tersuite-ai-app'), TERSUITE_AI_VERSION, true);

        // Modular components
        wp_register_script('tersuite-ai-assistant', TERSUITE_AI_URL . 'assets/js/assistant.js', array('jquery', 'tersuite-ai-api'), TERSUITE_AI_VERSION, true);
        wp_register_script('tersuite-ai-agent-activity', TERSUITE_AI_URL . 'assets/js/agent-activity.js', array('jquery', 'tersuite-ai-api'), TERSUITE_AI_VERSION, true);
        wp_register_script('tersuite-ai-file-tree', TERSUITE_AI_URL . 'assets/js/file-tree.js', array('jquery', 'tersuite-ai-api'), TERSUITE_AI_VERSION, true);
        wp_register_script('tersuite-ai-editor', TERSUITE_AI_URL . 'assets/js/editor.js', array('jquery', 'tersuite-ai-api'), TERSUITE_AI_VERSION, true);

        // Studio Shell Bundle
        wp_register_script('tersuite-ai-studio', TERSUITE_AI_URL . 'assets/js/ai-studio.js', array(
            'jquery',
            'tersuite-ai-app',
            'tersuite-ai-api',
            'tersuite-ai-websocket',
            'tersuite-ai-assistant',
            'tersuite-ai-agent-activity',
            'tersuite-ai-file-tree',
            'tersuite-ai-editor'
        ), TERSUITE_AI_VERSION, true);

        $project_id = isset($_GET['project_id']) ? sanitize_text_field(wp_unslash($_GET['project_id'])) : '';
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

        wp_localize_script('tersuite-ai-app', 'TersuiteAI', array(
            'ajaxUrl'           => admin_url('admin-ajax.php'),
            'nonce'             => wp_create_nonce(Tersuite_AI_Nonce::ACTION),
            'backendConfigured' => Tersuite_AI_Settings::get('backend_api_url') !== '',
            'backendApiUrl'     => rtrim((string)Tersuite_AI_Settings::get('backend_api_url'), '/'),
            'websocketUrl'      => (string)Tersuite_AI_Settings::get('websocket_base_url'),
            'projectId'         => $project_id,
            'page'              => $page,
        ));
    }

    public function enqueue($screen) {
        wp_enqueue_style('tersuite-ai-admin');
        wp_enqueue_style('tersuite-ai-studio');
        wp_enqueue_script('tersuite-ai-app');
        wp_enqueue_script('tersuite-ai-api');
        wp_enqueue_script('tersuite-ai-websocket');

        if ($screen === 'ai-studio') {
            wp_enqueue_script('tersuite-ai-assistant');
            wp_enqueue_script('tersuite-ai-agent-activity');
            wp_enqueue_script('tersuite-ai-file-tree');
            wp_enqueue_script('tersuite-ai-editor');
            wp_enqueue_script('tersuite-ai-studio');
        }
    }
}
