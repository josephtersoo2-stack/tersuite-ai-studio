<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Version_Manager { protected $api; public function __construct(){ $this->api=new Tersuite_AI_API_Client(); } public function list($id){ return $this->api->get('/api/projects/'.rawurlencode($id).'/versions/'); } public function restore($id,$version){ return $this->api->post('/api/projects/'.rawurlencode($id).'/versions/'.rawurlencode($version).'/restore/'); } }
