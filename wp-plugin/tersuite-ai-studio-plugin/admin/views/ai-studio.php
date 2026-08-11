<?php defined('ABSPATH') || exit; ?>
<div class="wrap tsa-wrap">
<?php include TERSUITE_AI_DIR.'admin/partials/sidebar.php'; ?>
<div class="tsa-main"><div class="tsa-topbar-spacer"></div><?php include TERSUITE_AI_DIR.'admin/partials/header.php'; ?>
<div class="tsa-content">
<div class="tsa-studio-head">
 <div><div class="tsa-kicker">AI STUDIO</div><h1 id="tsa-header-project-name">Loading Project...</h1>
 <div class="tsa-studio-meta"><span class="tsa-status-chip live" id="tsa-meta-status">In Context</span><span id="tsa-meta-type">Plugin</span><span id="tsa-meta-version">--</span><span id="tsa-meta-files">0 files</span></div></div>
 <div class="tsa-studio-actions"><button class="tsa-secondary" id="tsa-open-session-report">Session Summary</button><button class="tsa-primary" id="tsa-generate-now">Open Production Plan</button></div>
</div>
<div class="tsa-context-banner tsa-panel">
 <div><span class="tsa-kicker">PROJECT CONTEXT LOADED</span><strong>Coordinator ready for this project</strong><p>Original brief, approved plan, current files, previous sessions, validation results and WordPress site context are loaded for this studio session.</p></div>
 <div class="tsa-context-facts"><span id="tsa-fact-tasks">--/-- tasks complete</span><span id="tsa-fact-version">--</span><span id="tsa-fact-sandbox">Sandbox Pending</span></div>
</div>
<div class="tsa-studio-grid">
<section class="tsa-panel tsa-explorer">
  <div class="tsa-panel-head compact"><strong>EXPLORER</strong><div><button class="tsa-mini-btn" title="Refresh Files" id="tsa-refresh-files">↻</button></div></div>
  <div class="tsa-tree" id="tsa-file-tree">
    <div class="tsa-tree-loading" style="padding:16px; color:#94a3b8; font-size:12px;">Loading workspace manifest...</div>
  </div>
</section>
<section class="tsa-panel tsa-editor">
  <div class="tsa-editor-tabs"><button class="editor-tab active">Select a file <span>×</span></button><div class="editor-tools"><button class="tsa-btn-save-file" title="Save File (Ctrl+S)">💾 Save</button></div></div>
  <div class="tsa-breadcrumb">Select a file from the explorer to edit</div>
  <div class="tsa-code-wrap"><div style="padding:24px; color:#64748b; font-family:sans-serif; font-size:13px; text-align:center;">Select a file from the left workspace tree to view or edit code.</div></div>
</section>
<section class="tsa-panel tsa-assistant">
  <div class="tsa-panel-head compact"><strong>✦ TERSUITE COORDINATOR</strong><span class="tsa-live-dot">●</span></div>
  <div class="tsa-chat" id="tsa-chat">
    <div class="chat-system-context"><strong>PROJECT CONTEXT READY</strong><p>I know the project context, brief, approved plan, current files, and WordPress environment.</p></div>
  </div>
  <div class="tsa-chat-composer">
    <textarea id="tsa-chat-input" placeholder="Talk to the Tersuite Coordinator about this project…"></textarea>
    <div class="tsa-composer-bottom">
      <span class="tsa-coordinator-lock">✦ Coordinator only</span>
      <div><button class="tsa-send" id="tsa-send-chat">➤ Send</button></div>
    </div>
  </div>
</section>
</div>
<section class="tsa-agent-strip">
  <div class="tsa-agent-strip-head"><span>PRODUCTION TEAM · INTERNAL WORKERS</span><span class="tsa-agent-strip-note">Coordinator distributes tasks automatically</span></div>
  <div class="tsa-agent-cards">
    <div class="tsa-agent-card done"><b>✦</b><span>Coordinator</span><small>Planning / Orchestration</small></div>
  </div>
</section>
<div class="tsa-session-report tsa-panel">
  <div class="tsa-panel-head compact"><strong>LAST SESSION SUMMARY</strong><button class="tsa-ghost" id="tsa-toggle-session-report">Expand</button></div>
  <div class="tsa-session-report-body" id="tsa-session-report">
    <div class="tsa-summary-grid">
      <div><span>Completed</span><strong>-- tasks</strong></div>
      <div><span>Files changed</span><strong>--</strong></div>
      <div><span>Sandbox</span><strong class="success-text">--</strong></div>
      <div><span>Next step</span><strong>--</strong></div>
    </div>
    <div class="tsa-report-details">
      <div><b>Completed:</b> Loading session history...</div>
      <div><b>User action:</b> Select a plan to approve when ready.</div>
    </div>
  </div>
</div>
<div class="tsa-generation-footer"><div>Planning state</div><div class="tsa-progress big"><span style="width:0%"></span></div><strong id="tsa-footer-task-count">0/0</strong><span id="tsa-footer-proj-name">Project: Loading...</span><span>Context: ready</span></div>
</div></div></div>

<!-- Production Plan Review & Approval Modal Overlay -->
<div id="tsa-plan-modal" class="tsa-modal-overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.75); z-index:999999; display:none; align-items:center; justify-content:center;">
  <div class="tsa-modal-card" style="background:#0f172a; border:1px solid #334155; border-radius:12px; max-width:650px; width:90%; max-height:85vh; display:flex; flex-direction:column; padding:24px; color:#f8fafc;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #1e293b; padding-bottom:12px; margin-bottom:16px;">
      <h3 style="margin:0; font-size:18px; color:#38bdf8;">📋 Production Plan Review</h3>
      <button id="tsa-close-plan-modal" style="background:none; border:none; color:#94a3b8; font-size:20px; cursor:pointer;">×</button>
    </div>
    <div id="tsa-plan-modal-body" style="overflow-y:auto; flex:1; font-size:13px; line-height:1.6; color:#cbd5e1;">
      Loading production plan details...
    </div>
    <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid #1e293b; pt:16px; margin-top:16px;">
      <button class="tsa-btn tsa-secondary" id="tsa-modal-cancel">Cancel</button>
      <button class="tsa-btn tsa-primary" id="tsa-modal-approve-btn">Approve Production ⚡</button>
    </div>
  </div>
</div>
