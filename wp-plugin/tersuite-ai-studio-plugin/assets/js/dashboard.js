/**
 * Tersuite AI Studio — Dashboard View Script
 */
jQuery(function($) {
    'use strict';

    function loadDashboardData() {
        var $wrap = $('.tsa-dashboard-grid');
        if (!$wrap.length) return;

        TSAAPI.dashboard().done(function(res) {
            if (res && res.success && res.data) {
                var d = res.data;
                if (d.projects_count !== undefined) $('#tsa-stat-projects').text(d.projects_count);
                if (d.active_sessions_count !== undefined) $('#tsa-stat-sessions').text(d.active_sessions_count);
                if (d.deliveries_count !== undefined) $('#tsa-stat-deliveries').text(d.deliveries_count);
            }
        });
    }

    loadDashboardData();
});
