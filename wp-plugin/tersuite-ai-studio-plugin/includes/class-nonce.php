<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Nonce { const ACTION='tersuite_ai_nonce'; public static function field(){ return wp_nonce_field(self::ACTION,'tersuite_ai_nonce',true,false); } public static function verify($nonce=null){ return wp_verify_nonce($nonce !== null ? $nonce : (isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : ''), self::ACTION); } }
