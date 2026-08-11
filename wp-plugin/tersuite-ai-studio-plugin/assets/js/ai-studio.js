/**
 * Tersuite AI Studio — Main Studio Orchestrator
 */
jQuery(function($) {
    'use strict';

    var currentProject = (window.TersuiteAI && window.TersuiteAI.projectId) ? window.TersuiteAI.projectId : null;
    var currentPlanId = null;

    // 1. Initialize Sub-Components
    if (window.TSAAssistant) window.TSAAssistant.init(currentProject);
    if (window.TSAAgentActivity) window.TSAAgentActivity.init(currentProject);
    if (window.TSAFileTree) {
        window.TSAFileTree.init(currentProject, function(selectedPath) {
            if (window.TSAEditor) window.TSAEditor.loadFile(currentProject, selectedPath);
            if (window.TSAAssistant) window.TSAAssistant.setContext(selectedPath);
        });
    }
    if (window.TSAEditor) window.TSAEditor.init(currentProject);

    // 2. Load Initial Context on Studio Entry
    function loadStudioContext() {
        if (!currentProject) {
            if (window.TSA) window.TSA.toast('Open a project from Projects Registry to begin.', 'info');
            return;
        }

        if (window.TSA) window.TSA.toast('Loading project context...', 'info');

        TSAAPI.coordinatorContext(currentProject)
            .done(function(res) {
                if (res && res.success && res.data) {
                    var ctx = res.data;
                    updateContextBanner(ctx);

                    // Load workspace file tree
                    if (window.TSAFileTree) window.TSAFileTree.loadTree(currentProject);

                    // Load task graph / worker telemetry
                    loadTaskGraph();

                    // Load latest session report
                    loadSessionReport();
                }
            })
            .fail(function() {
                if (window.TSA) window.TSA.toast('Failed to load project context.', 'error');
            });
    }

    function updateContextBanner(ctx) {
        if (!ctx) return;
        if (ctx.project_name) $('.tsa-studio-head h1').text(ctx.project_name);
        if (ctx.version) $('.tsa-studio-meta span').eq(2).text(ctx.version);
        if (ctx.file_count !== undefined) $('.tsa-studio-meta span').eq(3).text(ctx.file_count + ' files');

        if (ctx.completed_tasks !== undefined && ctx.total_tasks !== undefined) {
            $('.tsa-context-facts span').eq(0).text(ctx.completed_tasks + '/' + ctx.total_tasks + ' tasks complete');
            $('.tsa-generation-footer strong').text(ctx.completed_tasks + '/' + ctx.total_tasks);
        }
        if (ctx.sandbox_status) $('.tsa-context-facts span').eq(2).text('Sandbox ' + ctx.sandbox_status);

        if (ctx.current_plan_id) currentPlanId = ctx.current_plan_id;
    }

    function loadTaskGraph() {
        if (!currentProject) return;
        TSAAPI.taskGraph(currentProject).done(function(res) {
            if (res && res.success && res.data && window.TSAAgentActivity) {
                window.TSAAgentActivity.updateTelemetry(res.data);
            }
        });
    }

    function loadSessionReport() {
        if (!currentProject) return;
        TSAAPI.sessionReports(currentProject).done(function(res) {
            if (res && res.success && res.data) {
                renderSessionReport(res.data);
            }
        });
    }

    function renderSessionReport(report) {
        var $body = $('#tsa-session-report');
        if (!$body.length || !report) return;

        var html = '<div class="tsa-summary-grid">' +
            '<div><span>Completed</span><strong>' + (report.completed_tasks_count || 0) + ' tasks</strong></div>' +
            '<div><span>Files changed</span><strong>' + (report.files_changed_count || 0) + '</strong></div>' +
            '<div><span>Sandbox</span><strong class="success-text">' + (report.sandbox_status || 'Passed') + '</strong></div>' +
            '<div><span>Next step</span><strong>' + $('<div/>').text(report.next_step || 'Review').html() + '</strong></div>' +
            '</div>' +
            '<div class="tsa-report-details">' +
            '<div><b>Completed:</b> ' + $('<div/>').text(report.completed_summary || 'None').html() + '</div>' +
            '<div><b>Remaining:</b> ' + $('<div/>').text(report.remaining_summary || 'None').html() + '</div>' +
            '<div><b>User action:</b> ' + $('<div/>').text(report.user_action_required || 'No action required.').html() + '</div>' +
            '</div>';

        $body.html(html);
    }

    // 3. Bind Action Controls
    $('#tsa-generate-now, #tsa-view-plan').off('click').on('click', function(e) {
        e.preventDefault();
        if (!currentProject) return;
        TSAAPI.getPlan(currentProject, currentPlanId).done(function(res) {
            if (res && res.success && res.data) {
                var plan = res.data;
                var msg = '📋 Production Plan #' + (plan.id || '1') + ':\n\n' + (plan.description || plan.summary || JSON.stringify(plan, null, 2));
                alert(msg);
            } else {
                if (window.TSA) window.TSA.toast('No active production plan found for review.', 'info');
            }
        });
    });

    // Explicit User Approval Handler
    $(document).off('click', '#tsa-approve-production, .tsa-btn-approve-plan').on('click', '#tsa-approve-production, .tsa-btn-approve-plan', function(e) {
        e.preventDefault();
        if (!currentProject) {
            if (window.TSA) window.TSA.toast('Open a project first.', 'info');
            return;
        }

        var planIdToApprove = $(this).attr('data-plan-id') || currentPlanId || 'latest';

        if (!window.confirm('Approve the production plan (ID: ' + planIdToApprove + ') and authorize Django to execute production?')) {
            return;
        }

        TSAAPI.approvePlan(currentProject, planIdToApprove).done(function(res) {
            if (res && res.success) {
                if (window.TSA) window.TSA.toast('Production approved! Production session queued in backend.', 'success');
                loadTaskGraph();
            } else {
                var err = (res && res.data && res.data.message) ? res.data.message : 'Production approval failed.';
                if (window.TSA) window.TSA.toast(err, 'error');
            }
        });
    });

    $('#tsa-toggle-session-report, #tsa-open-session-report').off('click').on('click', function(e) {
        e.preventDefault();
        $('.tsa-session-report-body').toggleClass('is-open');
    });

    // 4. Bind Live WebSocket Events
    if (window.TSAWebSocket && window.TersuiteAI) {
        var wsUrl = window.TersuiteAI.websocketUrl;
        var apiKey = window.TersuiteAI.apiKey;

        if (wsUrl && currentProject) {
            window.TSAWebSocket.connect(wsUrl, currentProject, apiKey, function(evt) {
                if (!evt || !evt.event) return;

                switch (evt.event) {
                    case 'task.progress':
                    case 'task.completed':
                    case 'worker.progress':
                    case 'worker.completed':
                        loadTaskGraph();
                        break;
                    case 'file.created':
                    case 'file.modified':
                        if (window.TSAFileTree) window.TSAFileTree.loadTree(currentProject);
                        break;
                    case 'production.completed':
                    case 'session.report.created':
                        loadStudioContext();
                        break;
                }
            });
        }
    }

    // Run Context Load
    loadStudioContext();
});