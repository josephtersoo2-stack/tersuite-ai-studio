<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Ecosystem Background Swarm Task Queue Monitor</h1>
    
    <div style="background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #29292e; padding-bottom: 12px; margin-bottom: 20px;">
            <h2 style="color: #fff; margin: 0;">⚡ Live Cluster Thread Lanes Ledger</h2>
            <span id="ts-queue-pulse-indicator" style="font-size: 11px; font-family: monospace; color: #34d399; background: rgba(52, 211, 153, 0.1); padding: 4px 10px; border-radius: 20px; border: 1px solid #065f46;">📡 CHANNELS POLLING ACTIVE</span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-family: monospace; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid #3c3c43; color: #a7aaad;">
                        <th style="padding: 12px 8px;">Thread ID</th>
                        <th style="padding: 12px 8px;">Target Project Context</th>
                        <th style="padding: 12px 8px;">Assigned Core Agent</th>
                        <th style="padding: 12px 8px; width: 180px;">Task Metrics Progress</th>
                        <th style="padding: 12px 8px;">Thread Lifecycle State</th>
                        <th style="padding: 12px 8px;">Initialized Date</th>
                        <th style="padding: 12px 8px; text-align: right;">Emergency Control</th>
                    </tr>
                </thead>
                <tbody id="ts-global-queue-table-body">
                    <tr><td colspan="7" style="padding: 20px 8px; color: #a7aaad; font-style: italic;">Synchronizing runtime system metrics...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const S = window.TERSOSTUDIO_State || {};
    const restUrl = S.rest_url || '';
    const nonce = S.nonce || '';
    const tableBody = document.getElementById('ts-global-queue-table-body');
    const pulseNode = document.getElementById('ts-queue-pulse-indicator');

    function renderStatusBadge(status) {
        let bg = '#2a2a2e', text = '#a7aaad', border = '#3c3c43';
        if (status === 'running') { bg = 'rgba(234,179,8,0.1)'; text = '#eab308'; border = '#854d0e'; }
        else if (status === 'completed') { bg = 'rgba(16,185,129,0.1)'; text = '#10b981'; border = '#065f46'; }
        else if (status === 'failed') { bg = 'rgba(239,68,68,0.1)'; text = '#ef4444'; border = '#991b1b'; }
        
        return `<span style="padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; background: ${bg}; color: ${text}; border: 1px solid ${border}; text-transform: uppercase;">${status}</span>`;
    }

    function refreshQueueLedgerData() {
        pulseNode.textContent = '📡 FETCHING MATRIX METRICS...';
        pulseNode.style.color = '#eab308';
        
        const paramConnector = restUrl.indexOf('?') !== -1 ? '&' : '?';
        fetch(`${restUrl}/queue`, { headers: { 'X-WP-Nonce': nonce } })
        .then(res => res.json())
        .then(data => {
            pulseNode.textContent = '📡 CHANNELS POLLING ACTIVE';
            pulseNode.style.color = '#34d399';
            
            if (data.success && data.data && data.data.queue) {
                const rows = data.data.queue;
                if (rows.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="7" style="padding: 20px 8px; color: #a7aaad; font-style: italic;">No background tracking jobs recorded inside the server clusters ledger container templates.</td></tr>';
                    return;
                }

                tableBody.innerHTML = '';
                rows.forEach(job => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #29292e';
                    
                    const isKillable = job.status === 'running' || job.status === 'pending';
                    const killButton = isKillable 
                        ? `<button class="button ts-kill-switch-btn" data-id="${job.id}" style="background: #dc2626; color: #fff; border: none; font-weight: bold; font-family: monospace; font-size: 11px;">🚨 FORCE CANCEL</button>`
                        : `<span style="color: #4b5563; font-size: 11px; font-style: italic;">Inactive</span>`;

                    tr.innerHTML = `
                        <td style="padding: 14px 8px; color: #60a5fa;">#JOB_LN_${job.id}</td>
                        <td style="padding: 14px 8px; font-weight: bold;">${job.project_name || 'System Registry Core'} <span style="color:#4b5563; font-size:11px;">[ID: ${job.project_id}]</span></td>
                        <td style="padding: 14px 8px; color: #f43f5e; font-weight: bold;">${job.active_agent.toUpperCase()}</td>
                        <td style="padding: 14px 8px;">
                            <div style="width: 100%; background: #2a2a2e; height: 6px; border-radius: 3px; overflow: hidden; margin-bottom: 4px;">
                                <div style="width: ${job.progress}%; background: #2563eb; height: 100%; transition: width 0.4s;"></div>
                            </div>
                            <span style="font-size: 10px; color: #a7aaad;">${job.progress}% Metrics Yielded</span>
                        </td>
                        <td style="padding: 14px 8px;">${renderStatusBadge(job.status)}</td>
                        <td style="padding: 14px 8px; color: #a7aaad;">${job.created_at}</td>
                        <td style="padding: 14px 8px; text-align: right;">${killButton}</td>
                    `;
                    tableBody.appendChild(tr);
                });

                document.querySelectorAll('.ts-kill-switch-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const jId = this.getAttribute('data-id');
                        if (!confirm(`CRITICAL SWARM TERMINATION: Are you completely sure you want to trigger the emergency kill-switch against Job #${jId}? This forcefully cuts the active background generation thread context lines.`)) return;
                        
                        fetch(`${restUrl}/queue/cancel`, {
                            method: 'POST',
                            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
                            body: JSON.stringify({ job_id: parseInt(jId, 10) })
                        })
                        .then(res => res.json())
                        .then(resData => {
                            if (resData.success) {
                                refreshQueueLedgerData();
                            }
                        });
                    });
                });
            }
        });
    }

    refreshQueueLedgerData();
    setInterval(refreshQueueLedgerData, 2500);
});
</script>
