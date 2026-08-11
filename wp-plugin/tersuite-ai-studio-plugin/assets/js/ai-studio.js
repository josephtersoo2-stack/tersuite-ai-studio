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

    // 2. Load Context on Entry
    function loadStudioContext() {
        if (!currentProject) {
            $('#tsa-header-project-name').text('No Project Selected');
            if (window.TSA) window.TSA.toast('Open a project from Projects Registry to begin.', 'info');
            return;
        }

        if (window.TSA) window.TSA.toast('Loading project context...', 'info');

        TSAAPI.coordinatorContext(currentProject)
            .done(function(res) {
                if (res && res.success && res.data) {
                    var ctx = res.data;
                    updateContextBanner(ctx);
                    if (window.TSAFileTree) window.TSAFileTree.loadTree(currentProject);
                    loadTaskGraph();
                    loadSessionReport();
                } else {
                    $('#tsa-header-project-name').text('Project Context');
                }
            })
            .fail(function() {
                $('#tsa-header-project-name').text('Project Context (Offline)');
            });
    }

    function updateContextBanner(ctx) {
        if (!ctx) return;
        var pName = ctx.project_name || ctx.name || 'Project #' + currentProject;
        $('#tsa-header-project-name').text(pName);
        $('#tsa-footer-proj-name').text('Project: ' + pName);

        if (ctx.version) {
            $('#tsa-meta-version').text(ctx.version);
            $('#tsa-fact-version').text(ctx.version);
        }
        if (ctx.file_count !== undefined) $('#tsa-meta-files').text(ctx.file_count + ' files');

        if (ctx.completed_tasks !== undefined && ctx.total_tasks !== undefined) {
            var taskStr = ctx.completed_tasks + '/' + ctx.total_tasks + ' tasks complete';
            $('#tsa-fact-tasks').text(taskStr);
            $('#tsa-footer-task-count').text(ctx.completed_tasks + '/' + ctx.total_tasks);
            var pct = ctx.total_tasks > 0 ? Math.round((ctx.completed_tasks / ctx.total_tasks) * 100) : 0;
            $('.tsa-generation-footer .tsa-progress span').css('width', pct + '%');
        }

        if (ctx.sandbox_status) $('#tsa-fact-sandbox').text('Sandbox ' + ctx.sandbox_status);
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

    // 3. Modal Production Plan Review UI (Replaces alert())
    function openPlanModal(planId) {
        if (!currentProject) {
            if (window.TSA) window.TSA.toast('Open a project first.', 'info');
            return;
        }

        var targetPlanId = planId || currentPlanId;
        if (!targetPlanId) {
            if (window.TSA) window.TSA.toast('No active production plan ID available.', 'info');
            return;
        }

        $('#tsa-plan-modal-body').html('<div style="padding:20px; text-align:center;">Loading production plan (ID: ' + targetPlanId + ')...</div>');
        $('#tsa-plan-modal').css('display', 'flex');

        TSAAPI.getPlan(currentProject, targetPlanId).done(function(res) {
            if (res && res.success && res.data) {
                var plan = res.data;
                var planHtml = '<h4>Objective</h4><p>' + $('<div/>').text(plan.objective || plan.summary || 'Production implementation plan').html() + '</p>' +
                    '<h4>Tasks (' + (plan.tasks ? plan.tasks.length : 0) + ')</h4><ul>';
                if (plan.tasks && Array.isArray(plan.tasks)) {
                    for (var i = 0; i < plan.tasks.length; i++) {
                        planHtml += '<li><b>' + (plan.tasks[i].name || plan.tasks[i].title) + ':</b> ' + (plan.tasks[i].description || '') + '</li>';
                    }
                }
                planHtml += '</ul>' +
                    '<h4>Affected Files</h4><p>' + (plan.affected_files ? plan.affected_files.join(', ') : 'Determined by production crews') + '</p>';

                $('#tsa-plan-modal-body').html(planHtml);
            } else {
                $('#tsa-plan-modal-body').html('<div style="color:#f87171;">Could not load plan details from backend.</div>');
            }
        });
    }

    function closePlanModal() {
        $('#tsa-plan-modal').hide();
    }

    $('#tsa-generate-now, #tsa-view-plan').off('click').on('click', function(e) {
        e.preventDefault();
        openPlanModal(currentPlanId);
    });

    $('#tsa-close-plan-modal, #tsa-modal-cancel').off('click').on('click', function(e) {
        e.preventDefault();
        closePlanModal();
    });

    $('#tsa-modal-approve-btn, #tsa-approve-production, .tsa-btn-approve-plan').off('click').on('click', function(e) {
        e.preventDefault();
        var targetPlanId = $(this).attr('data-plan-id') || currentPlanId;

        if (!targetPlanId) {
            if (window.TSA) window.TSA.toast('No valid plan ID to approve.', 'error');
            return;
        }

        if (!window.confirm('Confirm explicit approval for Production Plan ID: ' + targetPlanId + '?')) {
            return;
        }

        TSAAPI.approvePlan(currentProject, targetPlanId).done(function(res) {
            closePlanModal();
            if (res && res.success) {
                if (window.TSA) window.TSA.toast('Production approved! Production session queued in backend.', 'success');
                loadTaskGraph();
            } else {
                var err = (res && res.data && res.data.message) ? res.data.message : 'Production approval failed.';
                if (window.TSA) window.TSA.toast(err, 'error');
            }
        });
    });

    $('#tsa-refresh-files').off('click').on('click', function(e) {
        e.preventDefault();
        if (window.TSAFileTree && currentProject) window.TSAFileTree.loadTree(currentProject);
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

    loadStudioContext();
});