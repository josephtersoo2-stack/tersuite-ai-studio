/**
 * Tersuite AI Studio — Global Activity Feed Script
 */
jQuery(function($) {
    'use strict';

    var projectId = (window.TersuiteAI && window.TersuiteAI.projectId) ? window.TersuiteAI.projectId : null;

    function loadActivity() {
        var $list = $('#tsa-activity-list');
        if (!$list.length) return;

        $list.html('<div style="padding:20px; color:#94a3b8; text-align:center;">Loading activity log...</div>');

        TSA.ajax('activity', { project_id: projectId || '' }).done(function(res) {
            if (res && res.success && res.data) {
                renderActivity(res.data);
            } else {
                $list.html('<div style="padding:20px; color:#64748b; text-align:center;">No recent activity logged.</div>');
            }
        }).fail(function() {
            $list.html('<div style="padding:20px; color:#f87171; text-align:center;">Failed to connect to backend activity log.</div>');
        });
    }

    function renderActivity(items) {
        var $list = $('#tsa-activity-list');
        var listArr = Array.isArray(items) ? items : (items.results || []);

        if (listArr.length === 0) {
            $list.html('<div style="padding:20px; color:#94a3b8; text-align:center;">No recent activity logged yet.</div>');
            return;
        }

        var html = '';
        for (var i = 0; i < listArr.length; i++) {
            var item = listArr[i];
            html += '<div class="tsa-card" style="margin-bottom:10px;">' +
                '<div style="display:flex; justify-content:space-between; align-items:center;">' +
                '<strong>' + $('<div/>').text(item.action || item.title || 'System Event').html() + '</strong>' +
                '<small style="color:#64748b;">' + $('<div/>').text(item.created_at || 'Recent').html() + '</small>' +
                '</div>' +
                '<p style="font-size:12px; color:#94a3b8; margin:4px 0;">' + $('<div/>').text(item.description || item.details || '').html() + '</p>' +
                '</div>';
        }

        $list.html(html);
    }

    loadActivity();
});
