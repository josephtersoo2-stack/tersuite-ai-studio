/**
 * Tersuite AI Studio — Versions Management Script
 */
jQuery(function($) {
    'use strict';

    var projectId = (window.TersuiteAI && window.TersuiteAI.projectId) ? window.TersuiteAI.projectId : null;

    function loadVersions() {
        var $list = $('#tsa-versions-list');
        if (!$list.length || !projectId) return;

        $list.html('<div style="padding:20px; color:#94a3b8; text-align:center;">Loading project versions...</div>');

        TSA.ajax('versions', { id: projectId }).done(function(res) {
            if (res && res.success && res.data) {
                renderVersions(res.data);
            } else {
                $list.html('<div style="padding:20px; color:#64748b; text-align:center;">No versions recorded for this project yet.</div>');
            }
        }).fail(function() {
            $list.html('<div style="padding:20px; color:#f87171; text-align:center;">Failed to load version history.</div>');
        });
    }

    function renderVersions(items) {
        var $list = $('#tsa-versions-list');
        var listArr = Array.isArray(items) ? items : (items.results || []);

        if (listArr.length === 0) {
            $list.html('<div style="padding:20px; color:#94a3b8; text-align:center;">No previous versions created yet.</div>');
            return;
        }

        var html = '';
        for (var i = 0; i < listArr.length; i++) {
            var v = listArr[i];
            html += '<div class="tsa-card">' +
                '<div style="display:flex; justify-content:space-between; align-items:center;">' +
                '<h4>Version ' + $('<div/>').text(v.version || ('v' + (i + 1))).html() + '</h4>' +
                '<small style="color:#94a3b8;">' + $('<div/>').text(v.created_at || 'Recent').html() + '</small>' +
                '</div>' +
                '<p style="font-size:12px; color:#94a3b8; margin:6px 0;">' + $('<div/>').text(v.summary || 'Version snapshot created after production session.').html() + '</p>' +
                '</div>';
        }

        $list.html(html);
    }

    loadVersions();
});
