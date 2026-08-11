<?php
defined('ABSPATH') || exit;
class Tersuite_AI_File_Manager {
    protected $api; public function __construct(){ $this->api=new Tersuite_AI_API_Client(); }
    private function project_base($id){ return '/api/projects/'.rawurlencode($id).'/files/'; }
    public function tree($project){ return $this->api->get($this->project_base($project)); }
    public function file($project,$path){ return $this->api->get($this->project_base($project).rawurlencode($path).'/'); }
}
