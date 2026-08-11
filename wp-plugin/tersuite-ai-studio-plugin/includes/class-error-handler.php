<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Error_Handler { public static function message($error){ if(is_wp_error($error)){ return $error->get_error_message(); } if(is_string($error)){ return sanitize_text_field($error); } return __('An unexpected error occurred.','tersuite-ai-studio'); } public static function json_error($error,$code='tersuite_error'){ wp_send_json_error(array('code'=>$code,'message'=>self::message($error))); } }
