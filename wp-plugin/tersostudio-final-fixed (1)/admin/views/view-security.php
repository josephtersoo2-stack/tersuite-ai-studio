<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Active Security Sentinel // Firewall Rules Matrix</h1>
    
    <div style="background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e; max-width: 800px;">
        <h2 style="color: #fff; margin-top: 0; margin-bottom: 12px; color: #ef4444;">⛨️ Swarm Agent Sandbox Isolation Locks</h2>
        <p style="color: #a7aaad; font-size: 13px; line-height: 1.5; margin-bottom: 24px;">
            Define high-sensitivity string signatures and file paths below. If a Swarm Agent attempts to scan, read, or generate variations affecting these terms, the <code>Path_Validator</code> guard will drop the thread context instantly at the filesystem gateway boundary.
        </p>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #f43f5e;">Hardlocked Sandbox No-Go Zones (Line-Separated):</label>
            <textarea id="ts-security-nogo-textarea" rows="8" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 13px; line-height: 1.6; resize: vertical; outline: none;" placeholder="wp-config.php
.env
wp-admin
wp-includes
.git
.htaccess
"></textarea>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center;">
            <button id="ts-save-security-rules-btn" class="button button-primary" style="height: 40px; padding: 0 24px; font-weight: bold; font-size: 13px; background: #dc2626; border: none; text-shadow: none;">Commit Firewall Constraints Matrix</button>
            <span id="ts-security-feedback" style="font-family: monospace; font-size: 12px; font-weight: bold;"></span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const S = window.TERSOSTUDIO_State || {};
    const nogoTxt = document.getElementById('ts-security-nogo-textarea');
    const saveBtn = document.getElementById('ts-save-security-rules-btn');
    const feedback = document.getElementById('ts-security-feedback');

    if (!nogoTxt || !saveBtn) return;

    // Load current sandbox rules
    fetch(`${S.rest_url}/settings`, {
        method: 'GET',
        headers: { 'X-WP-Nonce': S.nonce }
    })
    .then(res => res.json())
    .then(payload => {
        if (payload.success && payload.data && payload.data.settings) {
            nogoTxt.value = payload.data.settings.security_nogo_zones || '';
        }
    });

    // Commit modifications over standardized REST factory lines
    saveBtn.addEventListener('click', function() {
        saveBtn.disabled = true;
        feedback.textContent = 'Synchronizing security layers...';
        feedback.style.color = '#eab308';

        fetch(`${S.rest_url}/settings`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': S.nonce,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                security_nogo_zones: nogoTxt.value.trim()
            })
        })
        .then(res => res.json())
        .then(payload => {
            saveBtn.disabled = false;
            if (payload.success) {
                feedback.textContent = 'FIREWALL_LOCKED -> Security sentinel definitions compiled.';
                feedback.style.color = '#34d399';
            } else {
                feedback.textContent = 'REJECTED -> ' + payload.message;
                feedback.style.color = '#f43f5e';
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            feedback.textContent = 'EXCEPT -> ' + err.message;
            feedback.style.color = '#f43f5e';
        });
    });
});
</script>
