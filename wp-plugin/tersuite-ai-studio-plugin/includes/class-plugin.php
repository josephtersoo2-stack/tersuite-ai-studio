<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Plugin {
 private static $instance; public $admin_menu; public $ajax; public $assets;
 public static function instance(){ if(!self::$instance) self::$instance=new self(); return self::$instance; }
 private function __construct(){ $this->admin_menu=new Tersuite_AI_Admin_Menu(); $this->ajax=new Tersuite_AI_AJAX(); $this->assets=new Tersuite_AI_Asset_Manager(); add_action('admin_menu',array($this->admin_menu,'register')); add_action('wp_ajax_nopriv_tersuite_ai_noop','__return_false'); add_action('admin_enqueue_scripts',array($this->assets,'register'),5); add_action('wp_ajax_nopriv_tersuite_none','__return_false'); $this->ajax->register(); }
 public static function activate(){ if(!get_option('tersuite_ai_delete_on_uninstall')) update_option('tersuite_ai_delete_on_uninstall','0'); }
 public static function deactivate(){}
}
