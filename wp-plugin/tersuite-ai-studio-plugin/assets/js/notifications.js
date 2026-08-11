/**
 * Tersuite AI Studio — Notifications View Script
 */
jQuery(function($) {
    'use strict';

    function loadNotifications() {
        var $container = $('#tsa-notifications-list');
        if (!$container.length) return;

        $container.html('<div style="padding:20px; color:#94a3b8; text-align:center;">Loading notifications...</div>');

        TSAAPI.notifications().done(function(res) {
            if (res && res.success && res.data) {
                renderNotifications(res.data);
            } else {
                $container.html('<div style="padding:20px; color:#64748b; text-align:center;">No new notifications.</div>');
            }
        }).fail(function() {
            $container.html('<div style="padding:20px; color:#f87171; text-align:center;">Failed to fetch notifications from backend.</div>');
        });
    }

    function renderNotifications(items) {
        var $container = $('#tsa-notifications-list');
        var list = Array.isArray(items) ? items : (items.results || []);

        if (list.length === 0) {
            $container.html('<div style="padding:20px; color:#94a3b8; text-align:center;">No notifications at this time.</div>');
            return;
        }

        var html = '';
        for (var i = 0; i < list.length; i++) {
            var item = list[i];
            html += '<div class="tsa-card ' + (item.unread ? 'unread' : '') + '">' +
                '<div style="display:flex; justify-content:space-between; align-items:center;">' +
                '<strong>' + $('<div/>').text(item.title || 'Notification').html() + '</strong>' +
                '<small style="color:#64748b;">' + $('<div/>').text(item.created_at || 'Recent').html() + '</small>' +
                '</div>' +
                '<p style="font-size:13px; color:#cbd5e1; margin:6px 0;">' + $('<div/>').text(item.message || '').html() + '</p>' +
                '</div>';
        }

        $container.html(html);
    }

    loadNotifications();
});
