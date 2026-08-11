<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Settings {
    const OPTION = 'tersuite_ai_studio_options';
    public static function get_options() {
        $defaults = array(
            'backend_api_url' => '',
            'websocket_base_url' => '',
            'api_key' => '',
            'auto_refresh' => '1',
            'notify_generation_complete' => '1',
            'editor_font_size' => '13',
            'theme' => 'dark',
        );
        $opts = get_option(self::OPTION, array());
        return wp_parse_args(is_array($opts) ? $opts : array(), $defaults);
    }
    public static function get($key, $default = '') {
        $o=self::get_options(); return isset($o[$key]) ? $o[$key] : $default;
    }
    public static function register() {
        register_setting(self::OPTION, self::OPTION, array('sanitize_callback'=>array(__CLASS__, 'sanitize')));
        add_settings_section('tersuite_connection','Connection','__return_false','tersuite-ai-settings');
        $fields=array(
            'backend_api_url'=>array('Backend API URL','url'),
            'websocket_base_url'=>array('WebSocket Base URL','url'),
            'api_key'=>array('Tersuite API Key','text'),
        );
        foreach($fields as $key=>$f){ add_settings_field($key,'<label for="'.$key.'">'.esc_html($f[0]).'</label>',array(__CLASS__,'render_field'),'tersuite-ai-settings','tersuite_connection',array('key'=>$key,'type'=>$f[1])); }
    }
    public static function sanitize($input) {
        $out=self::get_options(); $input=is_array($input)?$input:array();
        $out['backend_api_url']=isset($input['backend_api_url'])?esc_url_raw(trim($input['backend_api_url'])):'';
        $out['websocket_base_url']=isset($input['websocket_base_url'])?esc_url_raw(trim($input['websocket_base_url'])):'';
        $out['api_key']=isset($input['api_key'])?sanitize_text_field($input['api_key']):'';
        $out['auto_refresh']=!empty($input['auto_refresh'])?'1':'0';
        $out['notify_generation_complete']=!empty($input['notify_generation_complete'])?'1':'0';
        $out['editor_font_size']=isset($input['editor_font_size'])?max(10,min(20,absint($input['editor_font_size']))):13;
        $out['theme']=isset($input['theme']) && in_array($input['theme'], array('dark','light'), true) ? $input['theme'] : self::get('theme','dark'); return $out;
    }
    public static function render_field($args){
        $key=$args['key']; $type=$args['type']; $value=self::get($key); $type=$type==='url'?'url':'text';
        printf('<input class="regular-text tersuite-field" type="%1$s" id="%2$s" name="%3$s[%2$s]" value="%4$s" autocomplete="off">',$type,esc_attr($key),esc_attr(self::OPTION),esc_attr($value));
    }
}
add_action('admin_init', array('Tersuite_AI_Settings','register'));
