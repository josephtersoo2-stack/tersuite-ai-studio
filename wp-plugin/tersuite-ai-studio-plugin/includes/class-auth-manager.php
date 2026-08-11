<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Auth_Manager {
    public function me(){ return (new Tersuite_AI_API_Client())->get('/api/me/'); }
    public function connected(){ $key=Tersuite_AI_Settings::get('api_key'); return $key!=='' && Tersuite_AI_Settings::get('backend_api_url')!==''; }
}
