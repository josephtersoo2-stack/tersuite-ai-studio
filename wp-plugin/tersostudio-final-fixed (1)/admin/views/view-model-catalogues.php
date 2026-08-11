<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Model Catalogues Engine // Datastore Configuration</h1>
    
    <div style="background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e; max-width: 800px;">
        <h3 style="color: #fff; margin-top: 0; margin-bottom: 12px; color: #60a5fa;">📃 Available Swarm Deployment Models Dictionary</h3>
        <p style="color: #a7aaad; font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
            Modify or append available Large Language Model string variants below. Map entries using a clean line-break separation contract formatting rule: <code>model-api-id|Display Name Identifier Label</code>.
        </p>
        
        <div style="margin-bottom: 20px;">
            <textarea id="ts-models-catalog-textarea" rows="10" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 13px; line-height: 1.5; resize: vertical; outline: none;" placeholder="gemini-2.5-pro|Google Gemini 2.5 Pro Engine"></textarea>
        </div>
        
        <div style="display: flex; justify-content: space-between; alignItems: center;">
            <button id="ts-save-catalog-btn" class="button button-primary" style="height: 38px; padding: 0 20px; font-weight: bold; background: #2563eb; border: none; text-shadow: none;">Save Catalog Dictionary Layout</button>
            <span id="ts-catalog-feedback" style="font-family: monospace; font-size: 12px; font-weight: bold;"></span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const S = window.TERSOSTUDIO_State || {};
    const txt = document.getElementById('ts-models-catalog-textarea');
    const btn = document.getElementById('ts-save-catalog-btn');
    const fb  = document.getElementById('ts-catalog-feedback');

    if (!txt || !btn) return;

    // Load active dictionary mappings from the standardized REST routes
    fetch(`${S.rest_url}/models`, {
        method: 'GET',
        headers: { 'X-WP-Nonce': S.nonce }
    })
    .then(res => res.json())
    .then(payload => {
        if (payload.success && payload.data && payload.data.raw_textarea) {
            txt.value = payload.data.raw_textarea;
        }
    })
    .catch(err => {
        fb.textContent = 'ERROR -> Connection failure: ' + err.message;
        fb.style.color = '#f43f5e';
    });

    // Commit revisions straight over uniform contract layers
    btn.addEventListener('click', function() {
        btn.disabled = true;
        fb.textContent = 'Updating dictionary vectors...';
        fb.style.color = '#eab308';

        fetch(`${S.rest_url}/models`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': S.nonce,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                raw_catalog_text: txt.value.trim()
            })
        })
        .then(res => res.json())
        .then(payload => {
            btn.disabled = false;
            if (payload.success) {
                fb.textContent = 'SUCCESS -> Catalogue layout recompiled and saved.';
                fb.style.color = '#34d399';
            } else {
                fb.textContent = 'REJECTED -> ' + payload.message;
                fb.style.color = '#f43f5e';
            }
        })
        .catch(err => {
            btn.disabled = false;
            fb.textContent = 'EXCEPT -> Thread fault: ' + err.message;
            fb.style.color = '#f43f5e';
        });
    });
});
</script>
