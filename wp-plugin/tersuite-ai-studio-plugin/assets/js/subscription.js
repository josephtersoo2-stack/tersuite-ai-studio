/**
 * Tersuite AI Studio — Subscription & Billing Script
 */
jQuery(function($) {
    'use strict';

    function loadSubscription() {
        var $subWrap = $('#tsa-subscription-wrap');
        if (!$subWrap.length) return;

        TSAAPI.subscription().done(function(res) {
            if (res && res.success && res.data) {
                renderSubscription(res.data);
            }
        });
    }

    function renderSubscription(data) {
        if (!data) return;
        var status = data.status || {};
        if (status.plan_name) $('#tsa-current-plan-name').text(status.plan_name);
        if (status.status) $('#tsa-current-plan-status').text(status.status);
    }

    loadSubscription();
});
