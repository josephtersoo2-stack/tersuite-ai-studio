<?php defined('ABSPATH') || exit; ?>
<div class="wrap tsa-wrap">
<?php include TERSUITE_AI_DIR.'admin/partials/sidebar.php'; ?>
<div class="tsa-main"><div class="tsa-topbar-spacer"></div><?php include TERSUITE_AI_DIR.'admin/partials/header.php'; ?>
<div class="tsa-content">

<!-- Welcome / Context Header -->
<div class="tsa-page-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
 <div>
  <h1 id="tsa-dash-welcome">Welcome back</h1>
  <p id="tsa-dash-sync-time" style="color:#94a3b8; font-size:13px; margin:4px 0 0 0;">Everything happening across your Tersuite workspace · Synchronizing...</p>
 </div>
 <div style="display:flex; gap:10px; align-items:center;">
  <button id="tsa-refresh-dashboard" class="tsa-btn tsa-secondary" title="Refresh Dashboard Widgets">↻ Refresh</button>
  <a class="tsa-btn tsa-primary" href="<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-projects')); ?>">+ New Project</a>
 </div>
</div>

<!-- 4 Summary Cards -->
<div class="tsa-stats-grid">
 <div class="tsa-stat-card clickable" id="tsa-card-projects" onclick="window.location.href='<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-projects')); ?>'">
  <span>PROJECTS</span>
  <strong id="tsa-dash-stat-projects">--</strong>
  <small id="tsa-dash-stat-projects-sub">Loading projects...</small>
 </div>
 <div class="tsa-stat-card clickable" id="tsa-card-production" onclick="window.location.href='<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-projects')); ?>'">
  <span>ACTIVE PRODUCTION</span>
  <strong id="tsa-dash-stat-production">--</strong>
  <small id="tsa-dash-stat-production-sub">Checking sessions...</small>
 </div>
 <div class="tsa-stat-card clickable" id="tsa-card-builds" onclick="window.location.href='<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-deliveries')); ?>'">
  <span>COMPLETED BUILDS</span>
  <strong id="tsa-dash-stat-builds">--</strong>
  <small id="tsa-dash-stat-builds-sub">Packages delivered</small>
 </div>
 <div class="tsa-stat-card clickable" id="tsa-card-credits" onclick="window.location.href='<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-usage')); ?>'">
  <span>CREDITS / USAGE</span>
  <strong id="tsa-dash-stat-credits">--</strong>
  <small id="tsa-dash-stat-credits-sub">Usage limit status</small>
 </div>
</div>

<!-- Main 2-Column Dashboard Grid -->
<div class="tsa-dashboard-grid" style="margin-top:20px;">

 <!-- Production Activity Panel -->
 <section class="tsa-panel tsa-current">
  <div class="tsa-panel-head">
   <div><span class="tsa-kicker">PRODUCTION ACTIVITY</span><h2>Active Sessions</h2></div>
   <span class="tsa-status-chip live" id="tsa-dash-prod-status">STANDBY</span>
  </div>
  <div id="tsa-dash-prod-body">
   <div class="tsa-overall">
    <div><strong id="tsa-dash-prod-pct">0%</strong><span>session progress</span></div>
    <div class="tsa-progress big"><span id="tsa-dash-prod-bar" style="width:0%"></span></div>
    <small id="tsa-dash-prod-task">No active production session running.</small>
   </div>
   <div class="tsa-mini-agents" id="tsa-dash-prod-workers">
    <div class="ok">✦ Coordinator</div>
   </div>
   <a class="tsa-btn tsa-secondary" id="tsa-dash-open-studio" href="<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-studio')); ?>" style="display:inline-block; margin-top:14px;">Open AI Studio →</a>
  </div>
 </section>

 <!-- System Health & Connection Status Panel -->
 <section class="tsa-panel">
  <div class="tsa-panel-head">
   <div><span class="tsa-kicker">SYSTEM HEALTH</span><h2>Connection Status</h2></div>
  </div>
  <div class="tsa-health" id="tsa-dash-health-list">
   <div><span class="health-dot good" id="dot-backend"></span>Backend API <strong id="lbl-backend">Checking...</strong></div>
   <div><span class="health-dot warn" id="dot-ws"></span>WebSocket <strong id="lbl-ws">Checking...</strong></div>
   <div><span class="health-dot good" id="dot-auth"></span>Authentication <strong id="lbl-auth">Checking...</strong></div>
   <div><span class="health-dot good" id="dot-wp"></span>WordPress Environment <strong id="lbl-wp">v<?php echo esc_html(get_bloginfo('version')); ?></strong></div>
  </div>
 </section>

</div>

<!-- Attention Required & Recent Projects Grid -->
<div class="tsa-dashboard-grid" style="margin-top:20px;">

 <!-- Attention Required Panel -->
 <section class="tsa-panel">
  <div class="tsa-panel-head">
   <div><span class="tsa-kicker">ACTION NEEDED</span><h2>Attention Required</h2></div>
  </div>
  <div id="tsa-dash-attention-list" style="padding:16px;">
   <div style="color:#94a3b8; font-size:13px;">Checking system items...</div>
  </div>
 </section>

 <!-- Recent Projects Panel -->
 <section class="tsa-panel">
  <div class="tsa-panel-head">
   <div><span class="tsa-kicker">RECENT PROJECTS</span><h2>Your Workspace</h2></div>
   <a href="<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-projects')); ?>">View All</a>
  </div>
  <div class="tsa-project-list" id="tsa-dash-recent-projects">
   <div style="padding:16px; color:#94a3b8; font-size:13px;">Loading recent projects...</div>
  </div>
 </section>

</div>

<!-- Recent Activity & Recent Deliveries Grid -->
<div class="tsa-dashboard-grid bottom" style="margin-top:20px;">

 <!-- Recent Activity -->
 <section class="tsa-panel">
  <div class="tsa-panel-head">
   <div><span class="tsa-kicker">ACTIVITY</span><h2>Latest Events</h2></div>
   <a href="<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-activity')); ?>">See All</a>
  </div>
  <div class="tsa-activity-list" id="tsa-dash-recent-activity">
   <div style="padding:16px; color:#94a3b8; font-size:13px;">Loading activity feed...</div>
  </div>
 </section>

 <!-- Recent Deliveries -->
 <section class="tsa-panel">
  <div class="tsa-panel-head">
   <div><span class="tsa-kicker">DELIVERIES</span><h2>Recent Packages</h2></div>
   <a href="<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-deliveries')); ?>">View Deliveries</a>
  </div>
  <div id="tsa-dash-recent-deliveries" style="padding:16px;">
   <div style="color:#94a3b8; font-size:13px;">Loading deliveries...</div>
  </div>
 </section>

</div>

</div></div></div>
