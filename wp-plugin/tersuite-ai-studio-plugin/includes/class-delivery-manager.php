<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Delivery_Manager { protected $api; public function __construct(){ $this->api=new Tersuite_AI_API_Client(); } public function list($id){ return $this->api->get('/api/projects/'.rawurlencode($id).'/deliveries/'); } public function deliver($id){ return $this->api->post('/api/projects/'.rawurlencode($id).'/deliver/'); } }
