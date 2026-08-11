/**
 * Tersuite AI Studio — Read-Only Worker Telemetry Component
 *
 * Responsibilities:
 * - Render internal worker progress and task execution status.
 * - Enforce strictly READ-ONLY telemetry presentation.
 * - NEVER provide UI buttons to start, reassign, or select individual CrewAI workers.
 */
(function($) {
    'use strict';

    window.TSAAgentActivity = {
        projectId: null,

        init: function(projectId) {
            this.projectId = projectId || (window.TersuiteAI ? window.TersuiteAI.projectId : null);
        },

        /**
         * Update worker telemetry cards from task graph or WebSocket payload.
         */
        updateTelemetry: function(taskGraphData) {
            if (!taskGraphData || !taskGraphData.workers) return;

            var $container = $('.tsa-agent-cards');
            if (!$container.length) return;

            var workers = taskGraphData.workers;
            var html = '';

            // Coordinator card (always present)
            html += '<div class="tsa-agent-card done"><b>✦</b><span>Coordinator</span><small>Planning / Orchestration</small></div>';

            for (var i = 0; i < workers.length; i++) {
                var w = workers[i];
                var cardClass = '';
                var statusText = w.status || 'Waiting on dependencies';

                if (w.status === 'completed' || w.status === 'done') {
                    cardClass = 'done';
                    statusText = 'Completed';
                } else if (w.status === 'running' || w.status === 'active') {
                    cardClass = 'active';
                    statusText = w.action || 'Working · parallel';
                } else if (w.status === 'failed') {
                    cardClass = 'failed';
                    statusText = 'Failed';
                }

                var progressHtml = '';
                if (w.progress !== undefined && w.progress !== null) {
                    var pct = Math.max(0, Math.min(100, parseInt(w.progress, 10)));
                    progressHtml = '<div class="tsa-progress"><span style="width:' + pct + '%"></span></div>';
                }

                var icon = w.icon || '◈';

                html += '<div class="tsa-agent-card ' + cardClass + '">' +
                    '<b>' + icon + '</b>' +
                    '<span>' + $('<div/>').text(w.name || 'Worker').html() + '</span>' +
                    '<small>' + $('<div/>').text(statusText).html() + '</small>' +
                    progressHtml +
                    '</div>';
            }

            $container.html(html);
        }
    };

})(jQuery);
