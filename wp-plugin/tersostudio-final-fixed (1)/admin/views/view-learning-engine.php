<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Learning Engine // Category Template Boilerplates</h1>
    
    <div style="display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 24px;">
        <div style="flex: 1; min-width: 320px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e;">
            <h3 style="color: #fff; margin-top: 0; margin-bottom: 12px; color: #60a5fa;">🧠 Seed Category Framework Templates</h3>
            <p style="color: #a7aaad; font-size: 12px; line-height: 1.5; margin-bottom: 16px;">
                Inject functional blueprints directly into long-term memory. When prompting the swarm to write variations, agents strictly enforce these structural patterns.
            </p>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:bold; margin-bottom:4px; color:#eab308;">Plugin Classification Category:</label>
                    <select id="ts-template-category-slug" style="width:100%; background:#2a2a2e; color:#fff; border:1px solid #3c3c43; padding:8px; border-radius:4px;">
                        <option value="e-commerce">E-Commerce (WooCommerce Extensions Template)</option>
                        <option value="editor-extension">Editor Extension (Gutenberg Blocks Shell)</option>
                        <option value="headless-service">Headless Service (WP REST Controllers Specification)</option>
                        <option value="administrative-utility">Administrative Utility (Settings Panels Structures)</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:bold; margin-bottom:4px; color:#a7aaad;">Reference Boilerplate Document Buffer:</label>
                    <textarea id="ts-template-reference-buffer" rows="8" placeholder="Paste standard framework initialization parameters, base filters registry requirements, or structural class layouts here..." style="width:100%; background:#2a2a2e; color:#fff; border:1px solid #3c3c43; padding:10px; border-radius:4px; font-family:monospace; font-size:12px; line-height:1.4; resize:none;"></textarea>
                </div>
                <button id="ts-save-category-blueprint-btn" class="button button-primary" style="font-weight:bold; height:36px;">Commit Framework to Memory Matrix</button>
            </div>
            <div id="ts-learning-engine-feedback" style="margin-top:12px; font-size:12px; font-weight:bold; font-family:monospace;"></div>
        </div>

        <div style="flex: 1; min-width: 300px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e;">
            <h3 style="color: #fff; margin-top: 0; margin-bottom: 20px;">Self-Healing Validation Resolutions</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-family: monospace; font-size: 12px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #3c3c43;">
                            <th style="padding: 12px 8px; color: #a7aaad;">Component</th>
                            <th style="padding: 12px 8px; color: #a7aaad;">Signature Hash</th>
                            <th style="padding: 12px 8px; color: #a7aaad; text-align: right;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="ts-learning-matrix-body">
                        <tr><td colspan="3" style="padding: 16px 8px; color: #a7aaad; font-style: italic;">Loading closed-loop dataset memory blocks...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const S = window.TERSOSTUDIO_State || {};
    const btn = document.getElementById('ts-save-category-blueprint-btn');
    const catSelect = document.getElementById('ts-template-category-slug');
    const bufferArea = document.getElementById('ts-template-reference-buffer');
    const feedback = document.getElementById('ts-learning-engine-feedback');
    const listBody = document.getElementById('ts-learning-matrix-body');

    if (btn) {
        btn.addEventListener('click', function() {
            const textValue = bufferArea.value.trim();
            if (!textValue) return;
            btn.disabled = true;
            feedback.textContent = '';

            fetch(`${S.rest_url}/chat/knowledge/add`, {
                method: 'POST',
                headers: { 'X-WP-Nonce': S.nonce, 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    keyword: catSelect.value + ' master framework category blueprint specifications structure guidelines',
                    tag: 'blueprint',
                    reference: 'CRITICAL BOILERPLATE GUIDELINES FOR ' + catSelect.options[catSelect.selectedIndex].text.toUpperCase() + ':\n\n' + textValue
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    feedback.textContent = 'SUCCESS -> Locked into system RAG matrices.';
                    feedback.style.color = '#34d399';
                    bufferArea.value = '';
                }
            });
        });
    }

    fetch(`${S.rest_url}/learning`, { headers: { 'X-WP-Nonce': S.nonce } })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.data && data.data.patterns) {
            listBody.innerHTML = '';
            if (data.data.patterns.length === 0) {
                listBody.innerHTML = '<tr><td colspan="3" style="padding:16px 8px; color:#666;">No validation errors registered.</td></tr>';
                return;
            }
            data.data.patterns.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td style="padding:8px;">${row.file_path}</td><td style="padding:8px;color:#f43f5e;">${row.error_signature}</td><td style="padding:8px;text-align:right;"><span style="background:#065f46;color:#34d399;padding:2px 6px;border-radius:4px;font-size:10px;">FIXED</span></td>`;
                listBody.appendChild(tr);
            });
        }
    });
});
</script>
