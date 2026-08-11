/**
 * Tersuite AI Studio — Projects Registry View Script
 */
jQuery(function($) {
    'use strict';

    function loadProjects() {
        var $list = $('#tsa-projects-list');
        if (!$list.length) return;

        TSAAPI.projects().done(function(res) {
            if (res && res.success && res.data) {
                renderProjectsList(res.data);
            }
        });
    }

    function renderProjectsList(data) {
        var $list = $('#tsa-projects-list');
        if (!data || (Array.isArray(data) && data.length === 0)) {
            $list.html('<div style="padding:20px; color:#94a3b8; text-align:center;">No projects created yet. Click "Create New Project" to get started.</div>');
            return;
        }

        var items = Array.isArray(data) ? data : (data.results || []);
        var html = '';

        for (var i = 0; i < items.length; i++) {
            var p = items[i];
            html += '<div class="tsa-card">' +
                '<div style="display:flex; justify-content:space-between; align-items:center;">' +
                '<h3>' + $('<div/>').text(p.name || 'Untitled Project').html() + '</h3>' +
                '<span class="tsa-status-chip live">' + (p.status || 'Active') + '</span>' +
                '</div>' +
                '<p style="font-size:13px; color:#94a3b8; margin:8px 0;">' + $('<div/>').text(p.description || 'No description provided.').html() + '</p>' +
                '<a href="admin.php?page=tersuite-ai-studio&project_id=' + p.id + '" class="tsa-btn tsa-primary" style="display:inline-block; margin-top:10px;">Launch AI Studio ↗</a>' +
                '</div>';
        }

        $list.html(html);
    }

    $('#tsa-create-project-form').on('submit', function(e) {
        e.preventDefault();
        var name = $('#tsa-proj-name').val().trim();
        var desc = $('#tsa-proj-desc').val().trim();

        if (!name) return;

        TSAAPI.createProject(name, desc).done(function(res) {
            if (res && res.success && res.data) {
                if (window.TSA) window.TSA.toast('Project created successfully!', 'success');
                window.location.href = 'admin.php?page=tersuite-ai-studio&project_id=' + (res.data.id || res.data.project_id);
            }
        });
    });

    loadProjects();
});
