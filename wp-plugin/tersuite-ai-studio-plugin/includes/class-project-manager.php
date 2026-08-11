<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Project_Manager {
    protected $api; public function __construct(){ $this->api=new Tersuite_AI_API_Client(); }
    public function list($params=array()){ $path='/api/projects/'; if($params){ $path.='?'.http_build_query($params); } return $this->api->get($path); }
    public function get($id){ return $this->api->get('/api/projects/'.rawurlencode($id).'/'); }
    public function create($name,$description='',$type='plugin'){ return $this->api->post('/api/projects/',array('name'=>$name,'description'=>$description,'type'=>$type)); }
    public function update($id,$data){ return $this->api->put('/api/projects/'.rawurlencode($id).'/',$data); }
    public function delete($id){ return $this->api->delete('/api/projects/'.rawurlencode($id).'/'); }
}
