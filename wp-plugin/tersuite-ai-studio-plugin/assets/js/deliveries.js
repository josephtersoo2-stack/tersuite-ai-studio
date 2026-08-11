/**
 * Tersuite AI Studio — Deliveries View Script
 */
jQuery(function($) {
    'use strict';

    var projectId = (window.TersuiteAI && window.TersuiteAI.projectId) ? window.TersuiteAI.projectId : null;

    function loadDeliveries() {
        var $list = $('#tsa-deliveries-list');
        if (!$list.length || !projectId) return;

        TSAAPI.deliveries(projectId).done(function(res) {
            if (res && res.success && res.data) {
                renderDeliveries(res.data);
            }
        });
    }

    function renderDeliveries(items) {
        var $list = $('#tsa-deliveries-list');
        var listArr = Array.isArray(items) ? items : (items.results || []);

        if (listArr.length === 0) {
            $list.html('<div style="padding:20px; color:#94a3b8; text-align:center;">No deliveries ready for this project yet. Approved & sandbox-validated packages will appear here.</div>');
            return;
        }

        var html = '';
        for (var i = 0; i < listArr.length; i++) {
            var d = listArr[i];
            html += '<div class="tsa-card">' +
                '<div style="display:flex; justify-content:space-between; align-items:center;">' +
                '<h4>Package v' + (d.version || '1.0.0') + ' (' + (d.created_at || 'Recent') + ')</h4>' +
                '<span class="tsa-status-chip live">Validated</span>' +
                '</div>' +
                '<p style="font-size:12px; color:#94a3b8; margin:6px 0;">' + $('<div/>').text(d.summary || 'Validated WordPress plugin package ready for deployment.').html() + '</p>' +
                '<button class="tsa-btn tsa-primary tsa-btn-download-pkg" data-id="' + d.id + '">Download ZIP 📦</button>' +
                '</div>';
        }

        $list.html(html);
    }

    loadDeliveries();
});
