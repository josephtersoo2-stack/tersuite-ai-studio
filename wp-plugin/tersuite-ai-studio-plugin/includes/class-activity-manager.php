<?php
defined('ABSPATH') || exit; class Tersuite_AI_Activity_Manager { protected $api; public function __construct(){ $this->api=new Tersuite_AI_API_Client(); } public function list($project=null){ return $project?(new Tersuite_AI_Generation_Manager())->activity($project):$this->api->get('/api/activity/'); } }
