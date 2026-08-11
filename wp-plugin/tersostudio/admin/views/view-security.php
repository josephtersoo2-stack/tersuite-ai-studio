<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap" style="max-width: 960px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <h1 style="color: #0f172a; font-weight: 800; font-size: 26px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        🔑 Backend API Credentials & LLM Diagnostics
    </h1>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Card 1: Backend Credentials -->
        <div style="background: #0f172a; color: #f8fafc; padding: 24px; border-radius: 12px; border: 1px solid #1e293b; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
            <h2 style="margin-top: 0; margin-bottom: 8px; font-size: 17px; font-weight: 700; color: #38bdf8;">
                🔌 Connect to Django Backend
            </h2>
            <p style="color: #94a3b8; font-size: 12px; line-height: 1.5; margin-bottom: 20px;">
                Configure your Django API backend server URL and API Authentication Token.
            </p>

            <form id="ts-api-credentials-form" style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label for="ts-backend-url" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 6px; color: #cbd5e1;">
                        Backend API URL *
                    </label>
                    <input
                        type="url"
                        id="ts-backend-url"
                        required
                        placeholder="http://127.0.0.1:8000/api"
                        style="width: 100%; background: #020617; color: #38bdf8; border: 1px solid #334155; padding: 10px; border-radius: 8px; font-family: monospace; font-size: 12px; box-sizing: border-box;"
                    />
                </div>

                <div>
                    <label for="ts-api-key" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 6px; color: #cbd5e1;">
                        API Key / DRF Token *
                    </label>
                    <input
                        type="text"
                        id="ts-api-key"
                        required
                        placeholder="ec33c4db14d5bffcc6d3c8c0e81595e3bd020622"
                        style="width: 100%; background: #020617; color: #10b981; border: 1px solid #334155; padding: 10px; border-radius: 8px; font-family: monospace; font-size: 12px; box-sizing: border-box;"
                    />
                </div>

                <div style="display: flex; gap: 10px; margin-top: 6px;">
                    <button
                        type="submit"
                        id="ts-save-credentials-btn"
                        class="button button-primary"
                        style="background: #4f46e5; border: none; font-weight: bold; height: 38px; padding: 0 16px; border-radius: 6px; cursor: pointer; font-size: 12px;"
                    >
                        💾 Save Settings
                    </button>
                    <button
                        type="button"
                        id="ts-test-connection-btn"
                        class="button"
                        style="background: #1e293b; color: #38bdf8; border: 1px solid #334155; font-weight: bold; height: 38px; padding: 0 14px; border-radius: 6px; cursor: pointer; font-size: 12px;"
                    >
                        ⚡ Test Connection
                    </button>
                </div>
            </form>
            <div id="ts-connection-feedback" style="margin-top: 16px; font-size: 12px; font-weight: 700;"></div>
        </div>

        <!-- Card 2: LLM Model Connectivity Test -->
        <div style="background: #0f172a; color: #f8fafc; padding: 24px; border-radius: 12px; border: 1px solid #1e293b; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
            <h2 style="margin-top: 0; margin-bottom: 8px; font-size: 17px; font-weight: 700; color: #a855f7;">
                🤖 AI Model & LLM Test System
            </h2>
            <p style="color: #94a3b8; font-size: 12px; line-height: 1.5; margin-bottom: 20px;">
                Verify real-time connectivity to AI Model providers (Claude / OpenAI / Gemini / Swarm).
            </p>

            <button
                type="button"
                id="ts-test-llm-btn"
                class="button"
                style="width: 100%; background: #7c3aed; color: #fff; border: none; font-weight: bold; height: 42px; border-radius: 8px; cursor: pointer; font-size: 13px;"
            >
                🧪 Run AI Model Connection Test
            </button>

            <div id="ts-llm-feedback" style="margin-top: 16px; font-size: 12px; background: #020617; padding: 14px; border-radius: 8px; border: 1px solid #1e293b; min-height: 120px; font-family: monospace; overflow-x: auto;">
                <span style="color: #64748b; font-style: italic;">Click button above to test active AI model provider integration.</span>
            </div>
        </div>
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

    const testLlmBtn      = document.getElementById('ts-test-llm-btn');
    const llmFeedback     = document.getElementById('ts-llm-feedback');

    function loadCredentials() {
        fetch(restUrl + '/settings', { headers: { 'X-WP-Nonce': nonce } })
        .then(res => res.json())
        .then(data => {
            if (data.data && data.data.settings) {
                backendUrlInput.value = data.data.settings.backend_url || 'http://127.0.0.1:8000/api';
                apiKeyInput.value     = data.data.settings.api_key || 'ec33c4db14d5bffcc6d3c8c0e81595e3bd020622';
            }
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        feedback.textContent = 'Saving credentials...';
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
                feedback.textContent = '✓ Credentials saved!';
                feedback.style.color = '#10b981';
            } else {
                feedback.textContent = 'Error: ' + data.message;
                feedback.style.color = '#ef4444';
            }
        });
    });

    testBtn.addEventListener('click', function() {
        feedback.textContent = 'Testing connection...';
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

    testLlmBtn.addEventListener('click', function() {
        llmFeedback.innerHTML = '<span style="color:#f59e0b;">⏳ Testing AI Model / LLM Provider status...</span>';
        
        fetch(restUrl + '/settings/test-llm', {
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'online' || data.success) {
                llmFeedback.innerHTML = `
                    <div style="color:#10b981; font-weight:bold; margin-bottom:6px;">✅ AI Model Status: ${data.status || 'ONLINE'}</div>
                    <div style="color:#cbd5e1;">Active Model: <span style="color:#38bdf8; font-weight:bold;">${data.active_model || 'Claude/Gemini/OpenAI'}</span></div>
                    <div style="color:#cbd5e1;">Latency: <span style="color:#f59e0b;">${data.latency_ms} ms</span></div>
                    <div style="color:#cbd5e1; margin-top:4px;">Keys: Gemini=${data.api_keys?.google_gemini ? '✓' : '✗'} | Claude=${data.api_keys?.anthropic_claude ? '✓' : 'x'} | OpenAI=${data.api_keys?.openai ? '✓' : 'x'}</div>
                    <div style="color:#94a3b8; font-size:11px; margin-top:8px;">${data.message}</div>
                `;
            } else {
                llmFeedback.innerHTML = `<span style="color:#ef4444;">❌ LLM Connection Test Failed: ${data.message || JSON.stringify(data)}</span>`;
            }
        })
        .catch(err => {
            llmFeedback.innerHTML = `<span style="color:#ef4444;">❌ Connection Error: ${err.message}</span>`;
        });
    });

    loadCredentials();
});
</script>
