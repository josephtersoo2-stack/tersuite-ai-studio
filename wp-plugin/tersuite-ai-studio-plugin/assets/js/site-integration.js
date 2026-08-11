/**
 * Tersuite AI Studio — Site Integration Inspector Script
 */
jQuery(function($) {
    'use strict';

    function loadSiteInspection() {
        var $wrap = $('#tsa-site-inspector-info');
        if (!$wrap.length) return;

        TSAAPI.site().done(function(res) {
            if (res && res.success && res.data) {
                renderSiteInfo(res.data);
            }
        });
    }

    function renderSiteInfo(data) {
        if (!data) return;
        if (data.wordpress_version) $('#tsa-wp-ver').text(data.wordpress_version);
        if (data.php_version) $('#tsa-php-ver').text(data.php_version);
        if (data.site_url) $('#tsa-site-url').text(data.site_url);
        if (data.theme) $('#tsa-active-theme').text(data.theme);
        if (data.plugin_count !== undefined) $('#tsa-plugins-count').text(data.plugin_count);
    }

    $('#tsa-refresh-site-inspection').on('click', function(e) {
        e.preventDefault();
        loadSiteInspection();
    });

    loadSiteInspection();
});
