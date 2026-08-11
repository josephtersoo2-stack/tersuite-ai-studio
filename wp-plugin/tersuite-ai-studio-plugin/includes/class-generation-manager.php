<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Generation_Manager {
    protected $api; public function __construct(){ $this->api=new Tersuite_AI_API_Client(); }
    public function action($project,$verb){ return $this->api->post('/api/projects/'.rawurlencode($project).'/'.$verb.'/'); }
    public function generate($project,$prompt=''){ return $this->api->post('/api/projects/'.rawurlencode($project).'/generate/',array('prompt'=>$prompt)); }
    public function agents($project){ return $this->api->get('/api/projects/'.rawurlencode($project).'/agents/'); }
    public function activity($project){ return $this->api->get('/api/projects/'.rawurlencode($project).'/activity/'); }
}
