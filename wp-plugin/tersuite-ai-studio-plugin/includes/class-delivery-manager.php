<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Delivery_Manager {
    protected $api;
    public function __construct(){ $this->api=new Tersuite_AI_API_Client(); }
    private function base($id){return 'api/v1/projects/'.rawurlencode((string)$id).'/deliveries';}
    public function list($project_id){if(empty($project_id))return new WP_Error('missing_project_id',__('Project ID is required.','tersuite-ai-studio'));return $this->api->get($this->base($project_id));}
    public function get($project_id,$delivery_id){if(empty($project_id)||empty($delivery_id))return new WP_Error('invalid_params',__('Project and delivery IDs are required.','tersuite-ai-studio'));return $this->api->get($this->base($project_id).'/'.rawurlencode((string)$delivery_id));}
    public function deliver($project_id){if(empty($project_id))return new WP_Error('missing_project_id',__('Project ID is required.','tersuite-ai-studio'));return $this->api->post('api/v1/projects/'.rawurlencode((string)$project_id).'/deliver');}
    public function install($project_id,$delivery_id){
        $delivery=$this->get($project_id,$delivery_id); if(is_wp_error($delivery))return $delivery;
        $url=is_array($delivery)?($delivery['download_url']??$delivery['url']??($delivery['package']['download_url']??'')):'';
        if(!$url)return new WP_Error('delivery_url_missing',__('The backend did not return a downloadable package URL.','tersuite-ai-studio'));
        $parts=wp_parse_url($url);$base=wp_parse_url(Tersuite_AI_Settings::get('backend_api_url'));if(empty($parts['host'])||empty($base['host'])||strcasecmp($parts['host'],$base['host'])!==0)return new WP_Error('delivery_host_invalid',__('Package URL is not hosted by the configured Tersuite backend.','tersuite-ai-studio'));
        $headers=array('Accept'=>'application/zip');$key=Tersuite_AI_Settings::get('api_key');if($key)$headers['Authorization']='Bearer '.$key;
        $r=wp_remote_get($url,array('timeout'=>120,'headers'=>$headers,'sslverify'=>true));if(is_wp_error($r))return $r;$code=wp_remote_retrieve_response_code($r);if($code<200||$code>=300)return new WP_Error('download_failed',sprintf(__('Package download failed (HTTP %d).','tersuite-ai-studio'),$code));$body=wp_remote_retrieve_body($r);if(!$body)return new WP_Error('empty_package',__('The downloaded package was empty.','tersuite-ai-studio'));
        $tmp=wp_tempnam('tersuite-delivery');file_put_contents($tmp,$body);$result=(new Tersuite_AI_Plugin_Installer())->install($tmp,true);@unlink($tmp);return $result===true?array('installed'=>true):$result;
    }
}
