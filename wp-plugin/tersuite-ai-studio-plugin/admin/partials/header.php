<?php defined('ABSPATH') || exit; ?>
<div class="tsa-header">
  <div class="tsa-header-left">
    <button class="tsa-mobile-menu" aria-label="Open menu" type="button">☰</button>
    <div class="tsa-project-context">
      <span class="tsa-breadcrumb">Tersuite AI Studio</span><span class="tsa-chevron">›</span>
      <button class="tsa-project-select" id="tsa-project-select" type="button">Select project <span>⌄</span></button>
      <span class="tsa-status-dot" id="tsa-header-status-dot"></span><span class="tsa-status-text" id="tsa-header-status">Ready</span>
    </div>
  </div>
  <div class="tsa-header-right">
    <button class="tsa-generate-pill" id="tsa-global-generate" type="button"><span class="tsa-pulse"></span><span id="tsa-global-status">No active production</span></button>
    <button class="tsa-icon-btn tsa-theme-toggle" id="tsa-theme-toggle" aria-label="Switch theme" aria-pressed="false" type="button">☀</button><button class="tsa-icon-btn tsa-notify-btn" id="tsa-header-notifications" title="Notifications" type="button">♧<span class="tsa-badge" id="tsa-header-notification-count">0</span></button>
    <button class="tsa-user" id="tsa-user-menu" type="button"><span class="tsa-avatar" id="tsa-user-avatar">?</span><span><strong id="tsa-user-name">Account</strong><small id="tsa-user-plan">Not connected</small></span><span>⌄</span></button>
  </div>
</div>
<div class="tsa-project-dropdown" id="tsa-project-dropdown" hidden>
  <div class="tsa-project-dropdown-head"><strong>Projects</strong><button type="button" class="tsa-ghost" id="tsa-close-project-dropdown">×</button></div>
  <div id="tsa-project-dropdown-list"><div class="tsa-loading-inline">Loading projects…</div></div>
  <a class="tsa-dropdown-create" href="<?php echo esc_url(admin_url('admin.php?page=tersuite-ai-projects')); ?>">+ Manage projects</a>
</div>
