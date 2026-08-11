<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap" style="max-width: 900px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <h1 style="color: #0f172a; font-weight: 800; font-size: 26px; margin-bottom: 20px;">
        🔑 Backend API Credentials & Connection
    </h1>

    <div style="background: #0f172a; color: #f8fafc; padding: 28px; border-radius: 12px; border: 1px solid #1e293b; shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
        <h2 style="color: #fff; margin-top: 0; margin-bottom: 8px; font-size: 18px; font-weight: 700; color: #38bdf8;">
            🔌 Connect to Tersuite AI Studio Backend
        </h2>
        <p style="color: #94a3b8; font-size: 13px; line-height: 1.6; margin-bottom: 24px;">
            Configure your Django API backend server URL and API Authentication Token. All project requests, agent chats, and plugin builds route through this connection.
        </p>

        <form id="ts-api-credentials-form" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label for="ts-backend-url" style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 6px; color: #cbd5e1;">
                    Backend API URL *
                </label>
                <input
                    type="url"
                    id="ts-backend-url"
                    required
                    placeholder="http://127.0.0.1:8000/api"
                    style="width: 100%; background: #020617; color: #38bdf8; border: 1px solid #334155; padding: 12px; border-radius: 8px; font-family: monospace; font-size: 13px;"
                />
            </div>

            <div>
                <label for="ts-api-key" style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 6px; color: #cbd5e1;">
                    API Key / DRF Token *
                </label>
                <input
                    type="text"
                    id="ts-api-key"
                    required
                    placeholder="ec33c4db14d5bffcc6d3c8c0e81595e3bd020622"
                    style="width: 100%; background: #020617; color: #10b981; border: 1px solid #334155; padding: 12px; border-radius: 8px; font-family: monospace; font-size: 13px;"
                />
                <span style="font-size: 11px; color: #64748b; margin-top: 6px; display: block;">
                    Generate or copy your Token from the <a href="http://localhost:3000/api-keys" target="_blank" style="color: #818cf8;">User Web Dashboard ↗</a>
                </span>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 10px;">
                <button
                    type="submit"
                    id="ts-save-credentials-btn"
                    class="button button-primary"
                    style="background: #4f46e5; border: none; font-weight: bold; height: 42px; padding: 0 24px; border-radius: 8px; cursor: pointer;"
                >
                    💾 Save Credentials
                </button>
                <button
                    type="button"
                    id="ts-test-connection-btn"
                    class="button"
                    style="background: #1e293b; color: #38bdf8; border: 1px solid #334155; font-weight: bold; height: 42px; padding: 0 20px; border-radius: 8px; cursor: pointer;"
                >
                    ⚡ Test Backend Connection
                </button>
            </div>
        </form>

        <div id="ts-connection-feedback" style="margin-top: 20px; font-size: 13px; font-weight: 700;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const S = window.TERSOSTUDIO_State || {};
    const restUrl = S.rest_url || '/wp-json/tersostudio/v2';
    const nonce = S.nonce || '';

    const backendUrlInput = document.getElementById('ts-backend-url');
    const apiKeyInput     = document.getElementById('ts-api-key');
    const form            = document.getElementById('ts-api-credentials-form');
    const testBtn         = document.getElementById('ts-test-connection-btn');
    const feedback        = document.getElementById('ts-connection-feedback');

    function loadCredentials() {
        fetch(restUrl + '/settings', { headers: { 'X-WP-Nonce': nonce } })
        .then(res => res.json())
        .then(data => {
            if (data.data && data.data.settings) {
                backendUrlInput.value = data.data.settings.backend_url || 'http://localhost:8000/api';
                apiKeyInput.value     = data.data.settings.api_key || 'ec33c4db14d5bffcc6d3c8c0e81595e3bd020622';
            }
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        feedback.textContent = 'Saving API credentials...';
        feedback.style.color = '#f59e0b';

        fetch(restUrl + '/settings', {
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
            body: JSON.stringify({
                backend_url: backendUrlInput.value.trim(),
                api_key: apiKeyInput.value.trim()
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                feedback.textContent = '✓ Credentials saved successfully!';
                feedback.style.color = '#10b981';
            } else {
                feedback.textContent = 'Error: ' + data.message;
                feedback.style.color = '#ef4444';
            }
        });
    });

    testBtn.addEventListener('click', function() {
        feedback.textContent = 'Testing connection to Django API backend...';
        feedback.style.color = '#38bdf8';

        fetch(restUrl + '/settings/test-connection', {
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                feedback.textContent = '✓ ' + data.message;
                feedback.style.color = '#10b981';
            } else {
                feedback.textContent = '❌ ' + (data.message || 'Connection test failed');
                feedback.style.color = '#ef4444';
            }
        });
    });

    loadCredentials();
});
</script>
