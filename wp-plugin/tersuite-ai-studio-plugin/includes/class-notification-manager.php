<?php
defined('ABSPATH') || exit; class Tersuite_AI_Notification_Manager { protected $api; public function __construct(){ $this->api=new Tersuite_AI_API_Client(); } public function list(){ return $this->api->get('/api/notifications/'); } public function read($id){ return $this->api->post('/api/notifications/'.rawurlencode($id).'/read/'); } }
