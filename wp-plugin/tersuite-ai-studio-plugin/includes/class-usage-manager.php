<?php
defined('ABSPATH') || exit; class Tersuite_AI_Usage_Manager { protected $api; public function __construct(){ $this->api=new Tersuite_AI_API_Client(); } public function usage(){ return $this->api->get('/api/usage/'); } public function credits(){ return $this->api->get('/api/credits/'); } }
