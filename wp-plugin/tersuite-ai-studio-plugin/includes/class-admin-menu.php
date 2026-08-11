<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Admin_Menu {
 public function register(){
  $cap='manage_options'; $slug='tersuite-ai';
  add_menu_page('Tersuite AI Studio','Tersuite AI Studio',$cap,$slug,array($this,'render_dashboard'),'dashicons-admin-generic',58);
  $menus=array(
   'dashboard'=>array('Dashboard','render_dashboard'), 'projects'=>array('Projects','render_projects'),'ai-studio'=>array('AI Studio','render_ai_studio'),'generations'=>array('Generations','render_generations'),'files'=>array('Files','render_files'),'versions'=>array('Versions','render_versions'),'deliveries'=>array('Deliveries','render_deliveries'),'site-integration'=>array('Site Integration','render_site_integration'),'usage'=>array('Usage & Credits','render_usage'),'subscription'=>array('Subscription','render_subscription'),'activity'=>array('Activity','render_activity'),'notifications'=>array('Notifications','render_notifications'),'settings'=>array('Settings','render_settings')
  );
  foreach($menus as $slug2=>$item){ add_submenu_page($slug,$item[0],$item[0],$cap,$slug2==='dashboard'?$slug:$slug.'-'.$slug2,array($this,$item[1])); }
 }
 private function shell($title,$view,$extra=array()){
  Tersuite_AI_Capabilities::require_manage();
  $screen=$extra['screen']??'dashboard'; $asset=new Tersuite_AI_Asset_Manager(); $asset->enqueue($screen);
  $data=array_merge(array('title'=>$title,'screen'=>$screen,'options'=>Tersuite_AI_Settings::get_options()),$extra);
  extract($data,EXTR_SKIP); include TERSUITE_AI_DIR.'admin/views/'.$view.'.php';
 }
 public function render_dashboard(){ $this->shell('Dashboard','dashboard',array('screen'=>'dashboard')); }
 public function render_projects(){ $this->shell('Projects','projects',array('screen'=>'projects')); }
 public function render_ai_studio(){ $this->shell('AI Studio','ai-studio',array('screen'=>'ai-studio')); }
 public function render_generations(){ $this->shell('Generations','generations',array('screen'=>'generations')); }
 public function render_files(){ $this->shell('Files','files',array('screen'=>'files')); }
 public function render_versions(){ $this->shell('Versions','versions',array('screen'=>'versions')); }
 public function render_deliveries(){ $this->shell('Deliveries','deliveries',array('screen'=>'deliveries')); }
 public function render_site_integration(){ $this->shell('Site Integration','site-integration',array('screen'=>'site-integration')); }
 public function render_usage(){ $this->shell('Usage & Credits','usage',array('screen'=>'usage')); }
 public function render_subscription(){ $this->shell('Subscription','subscription',array('screen'=>'subscription')); }
 public function render_activity(){ $this->shell('Activity','activity',array('screen'=>'activity')); }
 public function render_notifications(){ $this->shell('Notifications','notifications',array('screen'=>'notifications')); }
 public function render_settings(){ $this->shell('Settings','settings',array('screen'=>'settings')); }
}
