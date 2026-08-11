<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Plugin_Installer {
    public function install($zip,$activate=false){
        if(!is_string($zip)||!file_exists($zip)) return new WP_Error('zip_missing',__('ZIP file is missing.','tersuite-ai-studio'));
        if(!class_exists('Plugin_Upgrader')) require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';
        if(!class_exists('Plugin_Upgrader')) return new WP_Error('upgrader_missing',__('WordPress Plugin Upgrader is unavailable.','tersuite-ai-studio'));
        $skin=new Automatic_Upgrader_Skin(); $upgrader=new Plugin_Upgrader($skin); $result=$upgrader->install($zip,array('overwrite_package'=>false));
        if(is_wp_error($result)) return $result; if(!$result) return new WP_Error('install_failed',__('WordPress could not install the generated plugin.','tersuite-ai-studio'));
        return true;
    }
}
