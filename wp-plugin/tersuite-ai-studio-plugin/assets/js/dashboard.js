/**
 * Tersuite AI Studio — Dynamic Dashboard Module Orchestrator
 *
 * Responsibilities:
 * - State management for independent Dashboard widgets.
 * - Dynamic data fetching from service layer without duplicating backend logic.
 * - Dynamic health checks, summary card counters, and attention required alerts.
 * - Global manual refresh handler and WebSocket live event integration with safe polling fallback.
 */
jQuery(function($) {
    'use strict';

    // 1. Dashboard State Manager
    var dashboardState = {
        loading: true,
        lastSync: null,
        projects: null,
        production: null,
        usage: null,
        activity: null,
        deliveries: null,
        health: null,
        attention: []
    };

    var pollInterval = null;

    // 2. Initialize Dashboard
    function initDashboard() {
        if (!$('#tsa-dash-welcome').length) return; // Not on dashboard view

        bindEvents();
        loadAllWidgets();

        // 3. Connect Live WebSocket Events if available
        if (window.TSAWebSocket && window.TersuiteAI && window.TersuiteAI.websocketUrl) {
            window.TSAWebSocket.connect(window.TersuiteAI.websocketUrl, 'dashboard', window.TersuiteAI.apiKey, function(evt) {
                if (!evt || !evt.event) return;

                switch (evt.event) {
                    case 'production.started':
                    case 'production.completed':
                    case 'task.progress':
                    case 'worker.progress':
                        loadProductionWidget();
                        break;
                    case 'file.modified':
                    case 'session.report.created':
                        loadActivityWidget();
                        break;
                }
            });
        }

        // Safe 45-second fallback polling
        pollInterval = setInterval(function() {
            loadProductionWidget();
            loadHealthWidget();
        }, 45000);
    }

    // 3. Bind UI Action Controls
    function bindEvents() {
        $('#tsa-refresh-dashboard').off('click').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            $btn.prop('disabled', true).text('↻ Refreshing...');
            loadAllWidgets(function() {
                $btn.prop('disabled', false).text('↻ Refresh');
                if (window.TSA) window.TSA.toast('Dashboard updated.', 'success');
            });
        });
    }

    // 4. Widget Data Loaders
    function loadAllWidgets(callback) {
        dashboardState.lastSync = new Date();
        updateSyncTime();

        var p1 = loadSummaryWidget();
        var p2 = loadProductionWidget();
        var p3 = loadHealthWidget();
        var p4 = loadRecentProjectsWidget();
        var p5 = loadActivityWidget();
        var p6 = loadDeliveriesWidget();

        $.when(p1, p2, p3, p4, p5, p6).always(function() {
            dashboardState.loading = false;
            if (typeof callback === 'function') callback();
        });
    }

    function updateSyncTime() {
        if (!dashboardState.lastSync) return;
        var timeStr = dashboardState.lastSync.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        $('#tsa-dash-sync-time').text('Everything happening across your Tersuite workspace · Last synchronized at ' + timeStr);
    }

    // Widget A: Overall Summary & Cards
    function loadSummaryWidget() {
        return TSAAPI.dashboard().done(function(res) {
            if (res && res.success && res.data) {
                var d = res.data;
                dashboardState.projects = d.projects || null;
                dashboardState.usage = d.usage || null;
                dashboardState.health = d.health || null;

                if (d.user && d.user.name) {
                    $('#tsa-dash-welcome').text('Welcome back, ' + d.user.name);
                }

                // Render Projects Card
                if (d.projects) {
                    $('#tsa-dash-stat-projects').text(d.projects.total !== undefined ? d.projects.total : '0');
                    $('#tsa-dash-stat-projects-sub').text((d.projects.active || 0) + ' active in workspace');
                }

                // Render Usage Card
                if (d.usage && d.usage.credits) {
                    var remaining = d.usage.credits.remaining !== undefined ? d.usage.credits.remaining : '--';
                    $('#tsa-dash-stat-credits').text(remaining);
                    $('#tsa-dash-stat-credits-sub').text('Credits remaining');
                } else {
                    $('#tsa-dash-stat-credits').text('Active');
                    $('#tsa-dash-stat-credits-sub').text('Subscription status');
                }

                renderAttentionWidget(d.attention || []);
            }
        });
    }

    // Widget B: Active Production Activity
    function loadProductionWidget() {
        var projectId = (window.TersuiteAI && window.TersuiteAI.projectId) ? window.TersuiteAI.projectId : '';

        return TSAAPI.taskGraph(projectId).done(function(res) {
            if (res && res.success && res.data) {
                var graph = res.data;
                var total = graph.total_tasks || 0;
                var completed = graph.completed_tasks || 0;
                var pct = total > 0 ? Math.round((completed / total) * 100) : 0;

                $('#tsa-dash-stat-production').text(graph.active_sessions_count || (graph.status === 'running' ? '1' : '0'));
                $('#tsa-dash-stat-production-sub').text(graph.status ? ('Status: ' + graph.status) : 'No active sessions');

                $('#tsa-dash-prod-pct').text(pct + '%');
                $('#tsa-dash-prod-bar').css('width', pct + '%');
                $('#tsa-dash-prod-status').text((graph.status || 'STANDBY').toUpperCase());
                $('#tsa-dash-prod-task').text(graph.current_task || 'No active task execution in progress.');

                if (graph.workers && Array.isArray(graph.workers) && window.TSAAgentActivity) {
                    window.TSAAgentActivity.updateTelemetry(graph);
                }
            } else {
                $('#tsa-dash-stat-production').text('0');
                $('#tsa-dash-stat-production-sub').text('No active production');
            }
        });
    }

    // Widget C: System Health Panel
    function loadHealthWidget() {
        return TSAAPI.testConnection().done(function(res) {
            var isConnected = (res && res.success);

            if (isConnected) {
                $('#dot-backend, #dot-auth').removeClass('warn bad').addClass('good');
                $('#lbl-backend').text('Healthy');
                $('#lbl-auth').text('Authenticated');
            } else {
                $('#dot-backend, #dot-auth').removeClass('good warn').addClass('bad');
                $('#lbl-backend').text('Offline / Error');
                $('#lbl-auth').text('Check Connection');
            }

            var wsReady = window.TersuiteAI && window.TersuiteAI.websocketUrl !== '';
            if (wsReady) {
                $('#dot-ws').removeClass('warn bad').addClass('good');
                $('#lbl-ws').text('Ready');
            } else {
                $('#dot-ws').removeClass('good bad').addClass('warn');
                $('#lbl-ws').text('Standby / Polling');
            }
        });
    }

    // Widget D: Recent Projects List
    function loadRecentProjectsWidget() {
        return TSAAPI.projects().done(function(res) {
            var $list = $('#tsa-dash-recent-projects');
            if (!res || !res.success || !res.data) {
                $list.html('<div style="padding:16px; color:#64748b; font-size:13px;">No projects found. <a href="admin.php?page=tersuite-ai-projects">+ Create Project</a></div>');
                return;
            }

            var items = Array.isArray(res.data) ? res.data : (res.data.results || []);
            if (items.length === 0) {
                $list.html('<div style="padding:16px; color:#64748b; font-size:13px;">No projects found. <a href="admin.php?page=tersuite-ai-projects">+ Create Project</a></div>');
                return;
            }

            var html = '';
            var slice = items.slice(0, 5);

            for (var i = 0; i < slice.length; i++) {
                var p = slice[i];
                var statusChip = p.status ? ('<span class="tsa-status-chip live">' + p.status + '</span>') : '<span class="tsa-status-chip neutral">Active</span>';

                html += '<div class="tsa-project-row" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-bottom:1px solid #1e293b;">' +
                    '<div style="display:flex; align-items:center; gap:10px;">' +
                    '<span class="tsa-folder-icon" style="color:#38bdf8;">◆</span>' +
                    '<div><strong style="color:#f8fafc; font-size:13px;">' + $('<div/>').text(p.name || 'Untitled').html() + '</strong><br><small style="color:#64748b; font-size:11px;">Updated ' + $('<div/>').text(p.updated_at || 'Recently').html() + '</small></div>' +
                    '</div>' +
                    '<div>' + statusChip + ' <a class="tsa-btn tsa-mini-btn" href="admin.php?page=tersuite-ai-studio&project_id=' + p.id + '" style="margin-left:8px; font-size:11px; text-decoration:none;">Studio ↗</a></div>' +
                    '</div>';
            }

            $list.html(html);
        });
    }

    // Widget E: Recent Activity Log
    function loadActivityWidget() {
        return TSAAPI.notifications().done(function(res) {
            var $list = $('#tsa-dash-recent-activity');
            if (!res || !res.success || !res.data) {
                $list.html('<div style="padding:16px; color:#64748b; font-size:13px;">No recent activity logged.</div>');
                return;
            }

            var items = Array.isArray(res.data) ? res.data : (res.data.results || []);
            if (items.length === 0) {
                $list.html('<div style="padding:16px; color:#64748b; font-size:13px;">No recent activity logged.</div>');
                return;
            }

            var html = '';
            var slice = items.slice(0, 5);

            for (var i = 0; i < slice.length; i++) {
                var act = slice[i];
                html += '<div style="display:flex; gap:12px; align-items:flex-start; padding:8px 0; border-bottom:1px solid #1e293b;">' +
                    '<span class="tsa-event-dot success" style="margin-top:6px;"></span>' +
                    '<div><strong style="color:#f8fafc; font-size:12px;">' + $('<div/>').text(act.title || act.action || 'System Event').html() + '</strong><br><small style="color:#64748b; font-size:11px;">' + $('<div/>').text(act.message || act.created_at || '').html() + '</small></div>' +
                    '</div>';
            }

            $list.html(html);
        });
    }

    // Widget F: Recent Deliveries
    function loadDeliveriesWidget() {
        var projectId = (window.TersuiteAI && window.TersuiteAI.projectId) ? window.TersuiteAI.projectId : '';

        return TSAAPI.deliveries(projectId).done(function(res) {
            var $wrap = $('#tsa-dash-recent-deliveries');
            if (!res || !res.success || !res.data) {
                $wrap.html('<div style="color:#64748b; font-size:13px;">No packages delivered yet. Completed builds will appear here.</div>');
                $('#tsa-dash-stat-builds').text('0');
                return;
            }

            var items = Array.isArray(res.data) ? res.data : (res.data.results || []);
            $('#tsa-dash-stat-builds').text(items.length);

            if (items.length === 0) {
                $wrap.html('<div style="color:#64748b; font-size:13px;">No packages delivered yet. Completed builds will appear here.</div>');
                return;
            }

            var html = '';
            var slice = items.slice(0, 3);

            for (var i = 0; i < slice.length; i++) {
                var d = slice[i];
                html += '<div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #1e293b;">' +
                    '<div><strong style="color:#38bdf8; font-size:12px;">v' + (d.version || '1.0.0') + '</strong> <span style="font-size:12px; color:#cbd5e1;">' + $('<div/>').text(d.summary || 'Plugin Package').html() + '</span></div>' +
                    '<a class="tsa-btn tsa-mini-btn" href="admin.php?page=tersuite-ai-deliveries" style="font-size:11px; text-decoration:none;">Download 📦</a>' +
                    '</div>';
            }

            $wrap.html(html);
        });
    }

    // Widget G: Attention Required Panel
    function renderAttentionWidget(items) {
        var $wrap = $('#tsa-dash-attention-list');
        if (!items || items.length === 0) {
            $wrap.html('<div style="color:#34d399; font-size:13px; font-weight:500;">✓ You\'re all caught up. No action is currently required.</div>');
            return;
        }

        var html = '';
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            html += '<div style="background:#1e293b; border:1px solid #334155; border-radius:8px; padding:12px; margin-bottom:10px;">' +
                '<strong style="color:#fbbf24; font-size:13px;">⚠️ ' + $('<div/>').text(item.title).html() + '</strong>' +
                '<p style="color:#cbd5e1; font-size:12px; margin:4px 0 8px 0;">' + $('<div/>').text(item.description).html() + '</p>' +
                (item.action_url ? '<a href="' + item.action_url + '" class="tsa-btn tsa-primary" style="font-size:11px; padding:4px 10px; display:inline-block; text-decoration:none;">' + $('<div/>').text(item.action_label || 'View Action').html() + '</a>' : '') +
                '</div>';
        }

        $wrap.html(html);
    }

    // Cleanup on window unload
    $(window).on('beforeunload', function() {
        if (pollInterval) clearInterval(pollInterval);
    });

    initDashboard();
});
