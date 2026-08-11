<?php defined('ABSPATH') || exit; ?>
<?php $current=$screen??'dashboard'; $items=array('dashboard'=>array('⌂','Dashboard'),'projects'=>array('□','Projects'),'ai-studio'=>array('✦','AI Studio'),'generations'=>array('◉','Generations'),'files'=>array('▣','Files'),'versions'=>array('◒','Versions'),'deliveries'=>array('⇩','Deliveries'),'site-integration'=>array('◈','Site Integration'),'usage'=>array('◔','Usage & Credits'),'subscription'=>array('▤','Subscription'),'activity'=>array('◌','Activity'),'notifications'=>array('♧','Notifications'),'settings'=>array('⚙','Settings')); ?>
<aside class="tsa-sidebar">
 <div class="tsa-brand"><span class="tsa-brand-mark">✦</span><span>Tersuite <b>AI Studio</b></span></div>
 <nav class="tsa-nav">
 <?php foreach($items as $slug=>$item): $url=$slug==='dashboard'?admin_url('admin.php?page=tersuite-ai'):admin_url('admin.php?page=tersuite-ai-'.$slug); ?>
   <a href="<?php echo esc_url($url); ?>" class="tsa-nav-item <?php echo $current===$slug?'is-active':''; ?>"><span class="tsa-nav-icon"><?php echo esc_html($item[0]); ?></span><span><?php echo esc_html($item[1]); ?></span><?php if($slug==='notifications'): ?><span class="tsa-nav-badge" id="tsa-sidebar-notification-count">0</span><?php endif; ?></a>
 <?php endforeach; ?>
 </nav>
 <div class="tsa-plan-card" id="tsa-plan-card">
  <div class="tsa-plan-top"><span id="tsa-sidebar-plan-name">PLAN</span><a href="<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-subscription')); ?>">Manage</a></div>
  <div class="tsa-plan-label">Credits</div><div class="tsa-plan-number"><span id="tsa-sidebar-credits">—</span> <span id="tsa-sidebar-credits-limit"></span></div><div class="tsa-progress"><span id="tsa-sidebar-credit-progress" style="width:0%"></span></div>
  <div class="tsa-plan-label tsa-gap">Generations</div><div class="tsa-plan-number tsa-small"><span id="tsa-sidebar-generations">—</span> <span id="tsa-sidebar-generation-limit"></span></div><div class="tsa-progress"><span id="tsa-sidebar-generation-progress" style="width:0%"></span></div>
  <a class="tsa-buy-btn" href="<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-subscription')); ?>">Manage credits</a>
 </div>
</aside>
