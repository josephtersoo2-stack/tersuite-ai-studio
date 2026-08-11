<?php
defined('ABSPATH') || exit;

class Tersuite_AI_Asset_Manager {
    public function register() {
        wp_register_style('tersuite-ai-admin', TERSUITE_AI_URL . 'assets/css/admin.css', array(), TERSUITE_AI_VERSION);
        wp_register_style('tersuite-ai-studio', TERSUITE_AI_URL . 'assets/css/ai-studio.css', array('tersuite-ai-admin'), TERSUITE_AI_VERSION);
        wp_register_script('tersuite-ai-app', TERSUITE_AI_URL . 'assets/js/app.js', array('jquery'), TERSUITE_AI_VERSION, true);
        wp_register_script('tersuite-ai-api', TERSUITE_AI_URL . 'assets/js/api.js', array('jquery','tersuite-ai-app'), TERSUITE_AI_VERSION, true);
        wp_register_script('tersuite-ai-websocket', TERSUITE_AI_URL . 'assets/js/websocket.js', array('jquery','tersuite-ai-app'), TERSUITE_AI_VERSION, true);
        $scripts = array('assistant','agent-activity','file-tree','editor','ai-studio','dashboard','projects','generations','files','versions','deliveries','site-integration','usage','subscription','activity','notifications','settings');
        foreach ($scripts as $name) {
            wp_register_script('tersuite-ai-'.$name, TERSUITE_AI_URL.'assets/js/'.$name.'.js', array('jquery','tersuite-ai-api'), TERSUITE_AI_VERSION, true);
        }
        $project_id = isset($_GET['project_id']) ? sanitize_text_field(wp_unslash($_GET['project_id'])) : '';
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        wp_localize_script('tersuite-ai-app', 'TersuiteAI', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(Tersuite_AI_Nonce::ACTION),
            'backendConfigured' => Tersuite_AI_Settings::get('backend_api_url') !== '' && Tersuite_AI_Settings::get('api_key') !== '',
            'backendApiUrl' => rtrim((string)Tersuite_AI_Settings::get('backend_api_url'), '/'),
            'websocketUrl' => (string)Tersuite_AI_Settings::get('websocket_base_url'),
            'projectId' => $project_id,
            'page' => $page,
            'settingsUrl' => admin_url('admin.php?page=tersuite-ai-settings'),
            'projectsUrl' => admin_url('admin.php?page=tersuite-ai-projects'),
            'studioUrl' => admin_url('admin.php?page=tersuite-ai-ai-studio'),
            'notificationsUrl' => admin_url('admin.php?page=tersuite-ai-notifications'),
            'usageUrl' => admin_url('admin.php?page=tersuite-ai-usage'),
            'subscriptionUrl' => admin_url('admin.php?page=tersuite-ai-subscription'),
        ));
    }

    public function enqueue($screen) {
        wp_enqueue_style('tersuite-ai-admin');
        if ($screen === 'ai-studio') wp_enqueue_style('tersuite-ai-studio');
        wp_enqueue_script('tersuite-ai-app');
        wp_enqueue_script('tersuite-ai-api');
        wp_enqueue_script('tersuite-ai-websocket');
        $map = array(
            'dashboard'=>'dashboard','projects'=>'projects','ai-studio'=>array('assistant','agent-activity','file-tree','editor','ai-studio'),
            'generations'=>'generations','files'=>'files','versions'=>'versions','deliveries'=>'deliveries','site-integration'=>'site-integration',
            'usage'=>'usage','subscription'=>'subscription','activity'=>'activity','notifications'=>'notifications','settings'=>'settings'
        );
        $names = isset($map[$screen]) ? (array)$map[$screen] : array();
        foreach ($names as $name) wp_enqueue_script('tersuite-ai-'.$name);
    }
}
