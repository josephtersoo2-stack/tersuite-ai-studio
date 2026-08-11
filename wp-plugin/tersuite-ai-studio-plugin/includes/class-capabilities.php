<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Capabilities { public static function manage(){ return current_user_can('manage_options'); } public static function require_manage(){ if(!self::manage()){ wp_die(esc_html__('You do not have permission to access Tersuite AI Studio.','tersuite-ai-studio'),403); } } }
