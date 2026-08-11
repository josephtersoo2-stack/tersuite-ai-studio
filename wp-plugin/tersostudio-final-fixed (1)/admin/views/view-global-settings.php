<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Global Settings Control Matrix // Platform Hardening Panels</h1>
    
    <div style="display: flex; gap: 24px; flex-wrap: wrap; max-width: 900px;">
        
        <div style="flex: 2; min-width: 400px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e;">
            <h3 style="color: #fff; margin-top: 0; margin-bottom: 16px; color: #60a5fa;">⚙️ Structural Boundary Resource Limits</h3>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 6px; color: #a7aaad;">Maximum Ingestion Code File Capacity Size Cap (KB):</label>
                    <input type="number" id="ts-global-max-size" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px; font-family: monospace;" />
                </div>
                
                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 6px; color: #a7aaad;">High-Frequency Polling Request Allowances Gate Threshold (Window 60s):</label>
                    <input type="number" id="ts-global-rate-poll" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px; font-family: monospace;" />
                </div>
                
                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 6px; color: #a7aaad;">Background Queue Vacuum Reclaim Window Timeout Interval (Seconds):</label>
                    <input type="number" id="ts-global-vacuum-time" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px; font-family: monospace;" />
                </div>
                
                <div style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <button id="ts-save-global-settings-btn" class="button button-primary" style="height: 38px; padding: 0 20px; font-weight: bold;">Commit Capacity Matrices</button>
                    <span id="ts-global-settings-feedback" style="font-family: monospace; font-size: 12px; font-weight: bold;"></span>
                </div>
            </div>
        </div>

        <div style="flex: 1; min-width: 280px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="color: #fff; margin-top: 0; margin-bottom: 12px; color: #f43f5e;">⚠️ Danger Zone</h3>
                <p style="color: #a7aaad; font-size: 12px; line-height: 1.5; margin-bottom: 20px;">
                    Executing a system reset completely purges relational database table schemas and strips physical workspace sandboxes from local disks. This action is non-reversible.
                </p>
            </div>
            
            <div>
                <button id="ts-factory-reset-system-btn" class="button" style="width: 100%; background: #27272a; color: #f43f5e; border: 1px solid #3f3f46; font-weight: bold; height: 40px; transition: background 0.2s;">Trigger Matrix Factory Reset</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const S = window.TERSOSTUDIO_State || {};
    const btnSave = document.getElementById('ts-save-global-settings-btn');
    const btnReset = document.getElementById('ts-factory-reset-system-btn');
    const fbText = document.getElementById('ts-global-settings-feedback');

    const inputSize = document.getElementById('ts-global-max-size');
    const inputRate = document.getElementById('ts-global-rate-poll');
    const inputVac  = document.getElementById('ts-global-vacuum-time');

    // Load present setup options dynamically via REST vectors
    fetch(`${S.rest_url}/settings`, { headers: { 'X-WP-Nonce': S.nonce } })
    .then(res => res.json())
    .then(payload => {
        if (payload.success && payload.data && payload.data.settings) {
            const s = payload.data.settings;
            inputSize.value = s.max_file_size || 1024;
            inputRate.value = s.rate_limit_poll || 120;
            inputVac.value  = s.vacuum_interval || 300;
        }
    });

    btnSave.addEventListener('click', function() {
        btnSave.disabled = true;
        fbText.textContent = 'Updating thresholds...';
        fbText.style.color = '#eab308';

        fetch(`${S.rest_url}/settings`, {
            method: 'POST',
            headers: { 'X-WP-Nonce': S.nonce, 'Content-Type': 'application/json' },
            body: JSON.stringify({
                max_file_size: parseInt(inputSize.value, 10),
                rate_limit_poll: parseInt(inputRate.value, 10),
                vacuum_interval: parseInt(inputVac.value, 10)
            })
        })
        .then(res => res.json())
        .then(payload => {
            btnSave.disabled = false;
            if (payload.success) {
                fbText.textContent = 'SUCCESS -> Hardening criteria committed.';
                fbText.style.color = '#34d399';
            }
        });
    });

    btnReset.addEventListener('click', function() {
        if (!confirm('CRITICAL ECOSYSTEM ALERT: This action drops all project records, unlinked snap streams, and deletes sandbox folders permanently from storage. Execute full factory environment reset?')) return;
        if (prompt('Type "PURGE" to attest security system invalidation credentials criteria:') !== 'PURGE') {
            alert('Reset cancelled: Attestation mismatch.');
            return;
        }

        btnReset.disabled = true;
        btnReset.textContent = 'Purging Cluster Channels...';

        fetch(`${S.rest_url}/settings/reset`, {
            method: 'POST',
            headers: { 'X-WP-Nonce': S.nonce, 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(payload => {
            if (payload.success) {
                alert('Ecosystem reset successfully finished. Reloading dashboard.');
                window.location.reload();
            } else {
                btnReset.disabled = false;
                btnReset.textContent = 'Trigger Matrix Factory Reset';
                alert('Reset failure: ' + payload.message);
            }
        });
    });
});
</script>
