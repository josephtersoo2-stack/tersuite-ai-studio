<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Specialist Agent Allocation Matrix // 10-Agent Swarm Hub</h1>
    
    <div style="display: flex; gap: 24px; flex-wrap: wrap; align-items: flex-start;">
        
        <form id="ts-keys-settings-form" style="flex: 1; min-width: 320px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e; box-sizing: border-box;">
            <h2 style="color: #fff; margin-top: 0; border-bottom: 1px solid #29292e; padding-bottom: 10px;">🔑 Gateway Credentials</h2>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">Google Gemini API Key</label>
                    <input type="password" id="ts-key-gemini" placeholder="AIzaSy..." style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 10px; border-radius: 4px; box-sizing: border-box;" />
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">OpenAI Secret API Key</label>
                    <input type="password" id="ts-key-openai" placeholder="sk-proj-..." style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 10px; border-radius: 4px; box-sizing: border-box;" />
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">Anthropic Claude API Key</label>
                    <input type="password" id="ts-key-claude" placeholder="sk-ant-..." style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 10px; border-radius: 4px; box-sizing: border-box;" />
                </div>
            </div>
            <div style="margin-top: 32px;">
                <button type="submit" id="ts-save-keys-btn" class="button button-primary" style="width: 100%; font-weight: bold; height: 40px;">Save Gateway Keys</button>
            </div>
        </form>

        <form id="ts-models-settings-form" style="flex: 2; min-width: 480px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e; box-sizing: border-box;">
            <h2 style="color: #fff; margin-top: 0; border-bottom: 1px solid #29292e; padding-bottom: 10px;">🤖 Specialized 10-Agent Swarm Allocations</h2>
            
            <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #38bdf8;">1. Master Architect (Conversational)</label>
                    <select id="ts-model-arch-simple" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #60a5fa;">2. Master Architect (Reasoning)</label>
                    <select id="ts-model-arch-reasoning" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #f43f5e;">3. Backend Engineer Agent</label>
                    <select id="ts-model-backend" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #ec4899;">4. Frontend Engineer Agent</label>
                    <select id="ts-model-frontend" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #c084fc;">5. Database Architect Agent</label>
                    <select id="ts-model-database" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #eab308;">6. Security Auditor Agent</label>
                    <select id="ts-model-security" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #fb923c;">7. Filesystem & Patch Engine</label>
                    <select id="ts-model-patch" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #a7aaad;">8. RAG Memory Orchestrator</label>
                    <select id="ts-model-memory" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #10b981;">9. QA & Validation Agent</label>
                    <select id="ts-model-qa" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #22c55e;">10. SRE DevOps & Runtime Monitor</label>
                    <select id="ts-model-devops" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #14b8a6;">Bonus Allocation: Learning & Framework Specialization Engine</label>
                    <select id="ts-model-learning" class="ts-model-selector" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;"></select>
                </div>
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" id="ts-save-models-btn" class="button button-primary" style="width: 100%; font-weight: bold; height: 42px; background: #2563eb; border: none; text-shadow: none;">Commit Cluster Allocation Matrix</button>
            </div>
        </form>
    </div>
    
    <div id="ts-swarm-msg-log" style="margin-top: 24px; padding: 14px; border-radius: 6px; background: #16161a; font-family: monospace; font-size: 13px; font-weight: bold; color: #a7aaad; max-width: 100%; border: 1px solid #29292e;">
        STATUS VECTOR -> Standby channels ready.
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const S = window.TERSOSTUDIO_State || {};
    const restUrl = S.rest_url || '';
    const nonce = S.nonce || '';

    const statusLog = document.getElementById('ts-swarm-msg-log');
    const keysForm = document.getElementById('ts-keys-settings-form');
    const modelsForm = document.getElementById('ts-models-settings-form');
    const saveKeysBtn = document.getElementById('ts-save-keys-btn');
    const saveModelsBtn = document.getElementById('ts-save-models-btn');

    function updateStatus(text, isError = false) {
        statusLog.textContent = `STATUS VECTOR -> ${text}`;
        statusLog.style.color = isError ? '#f43f5e' : '#34d399';
        statusLog.style.borderColor = isError ? '#7f1d1d' : '#065f46';
    }

    function loadCatalogData() {
        if (!restUrl) return;
        
        updateStatus('Synchronizing database registry files...');
        Promise.all([
            fetch(restUrl + '/models', { headers: { 'X-WP-Nonce': nonce } }),
            fetch(restUrl + '/settings', { headers: { 'X-WP-Nonce': nonce } })
        ])
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(([modelsData, settingsData]) => {
            if (modelsData.success && modelsData.data.models) {
                const selectors = document.querySelectorAll('.ts-model-selector');
                selectors.forEach(select => {
                    select.innerHTML = '';
                    modelsData.data.models.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = m.name;
                        select.appendChild(opt);
                    });
                });
            }
            if (settingsData.success && settingsData.data.settings) {
                const s = settingsData.data.settings;
                document.getElementById('ts-key-gemini').value = s.gemini_key || '';
                document.getElementById('ts-key-openai').value = s.openai_key || '';
                document.getElementById('ts-key-claude').value = s.claude_key || '';
                
                if (document.getElementById('ts-model-arch-simple'))    document.getElementById('ts-model-arch-simple').value = s.architect_simple || '';
                if (document.getElementById('ts-model-arch-reasoning')) document.getElementById('ts-model-arch-reasoning').value = s.architect_reasoning || '';
                if (document.getElementById('ts-model-backend'))        document.getElementById('ts-model-backend').value = s.backend_engineer || '';
                if (document.getElementById('ts-model-frontend'))       document.getElementById('ts-model-frontend').value = s.frontend_engineer || '';
                if (document.getElementById('ts-model-database'))       document.getElementById('ts-model-database').value = s.database_architect || '';
                if (document.getElementById('ts-model-security'))       document.getElementById('ts-model-security').value = s.security_auditor || '';
                if (document.getElementById('ts-model-patch'))          document.getElementById('ts-model-patch').value = s.patch_engine || '';
                if (document.getElementById('ts-model-memory'))         document.getElementById('ts-model-memory').value = s.memory_orchestrator || '';
                if (document.getElementById('ts-model-qa'))             document.getElementById('ts-model-qa').value = s.qa_validation || '';
                if (document.getElementById('ts-model-devops'))         document.getElementById('ts-model-devops').value = s.devops_monitor || '';
                if (document.getElementById('ts-model-learning'))       document.getElementById('ts-model-learning').value = s.learning_specialist || '';
                
                updateStatus('All 10 specialized agent context channels rehydrated safely.');
            }
        })
        .catch(err => {
            updateStatus(`Handshake failure: ${err.message}`, true);
        });
    }

    function transmitPayload(payload, buttonElement, originalText) {
        buttonElement.disabled = true;
        buttonElement.textContent = 'Committing changes...';
        updateStatus('Transmitting configuration matrix values over secure REST endpoints...');

        fetch(restUrl + '/settings', {
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            buttonElement.disabled = false;
            buttonElement.textContent = originalText;
            if (data.success) {
                updateStatus('Ecosystem configuration matrix encrypted and committed safely into database storage options.');
            } else {
                updateStatus(`Configuration update failure: ${data.message}`, true);
            }
        })
        .catch(err => {
            buttonElement.disabled = false;
            buttonElement.textContent = originalText;
            updateStatus(`Network operational transaction drop: ${err.message}`, true);
        });
    }

    function gatherFullStatePayload() {
        return {
            gemini_key: document.getElementById('ts-key-gemini').value.trim(),
            openai_key: document.getElementById('ts-key-openai').value.trim(),
            claude_key: document.getElementById('ts-key-claude').value.trim(),
            architect_simple: document.getElementById('ts-model-arch-simple').value,
            architect_reasoning: document.getElementById('ts-model-arch-reasoning').value,
            backend_engineer: document.getElementById('ts-model-backend').value,
            frontend_engineer: document.getElementById('ts-model-frontend').value,
            database_architect: document.getElementById('ts-model-database').value,
            security_auditor: document.getElementById('ts-model-security').value,
            patch_engine: document.getElementById('ts-model-patch').value,
            memory_orchestrator: document.getElementById('ts-model-memory').value,
            qa_validation: document.getElementById('ts-model-qa').value,
            devops_monitor: document.getElementById('ts-model-devops').value,
            learning_specialist: document.getElementById('ts-model-learning').value
        };
    }

    if (keysForm) {
        keysForm.addEventListener('submit', function(e) {
            e.preventDefault();
            transmitPayload(gatherFullStatePayload(), saveKeysBtn, 'Save Gateway Keys');
        });
    }

    if (modelsForm) {
        modelsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            transmitPayload(gatherFullStatePayload(), saveModelsBtn, 'Commit Cluster Allocation Matrix');
        });
    }

    loadCatalogData();
});
</script>
