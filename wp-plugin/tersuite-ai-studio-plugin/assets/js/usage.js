/**
 * Tersuite AI Studio — Usage & Credit Limits Script
 */
jQuery(function($) {
    'use strict';

    function loadUsage() {
        var $usageWrap = $('#tsa-usage-stats');
        if (!$usageWrap.length) return;

        TSAAPI.usage().done(function(res) {
            if (res && res.success && res.data) {
                renderUsage(res.data);
            }
        });
    }

    function renderUsage(data) {
        if (!data) return;
        var usage = data.usage || data;
        var credits = data.credits || {};

        if (usage.tokens_used !== undefined) $('#tsa-val-tokens').text(usage.tokens_used);
        if (usage.generations_count !== undefined) $('#tsa-val-generations').text(usage.generations_count);
        if (credits.remaining !== undefined) $('#tsa-val-credits').text(credits.remaining);
    }

    loadUsage();
});
