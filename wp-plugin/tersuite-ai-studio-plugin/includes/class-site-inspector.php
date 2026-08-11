<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Site_Inspector { public function inspect(){ global $wp_version; return array('wordpress_version'=>$wp_version,'php_version'=>PHP_VERSION,'site_url'=>home_url('/'),'admin_url'=>admin_url(),'multisite'=>is_multisite(),'theme'=>wp_get_theme()->get('Name'),'theme_version'=>wp_get_theme()->get('Version'),'woocommerce'=>defined('WC_VERSION')?WC_VERSION:null,'plugin_count'=>count(get_plugins())); } }
