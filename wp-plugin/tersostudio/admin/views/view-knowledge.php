<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Ecosystem Long-Term Memory Knowledge Base (RAG)</h1>
    
    <div style="display: flex; gap: 24px; flex-wrap: wrap; align-items: flex-start;">
        
        <div style="flex: 1; min-width: 320px; max-width: 440px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e;">
            <h3 style="color: #fff; margin-top: 0; margin-bottom: 16px;">🧠 Inject Semantic Context Block</h3>
            <form id="ts-rag-ingestion-form">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #60a5fa;">Lookup Query Trigger Keywords:</label>
                    <input type="text" id="ts-rag-keyword" placeholder="e.g. WooCommerce payment shortcode output style" required style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px 12px; border-radius: 4px; font-family: monospace; font-size: 13px;" />
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #eab308;">Context Vector Allocation Tag:</label>
                    <select id="ts-rag-tag" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;">
                        <option value="wp_core">wp_core (WordPress Core API Standards)</option>
                        <option value="blueprint">blueprint (Structural Architecture Layouts)</option>
                        <option value="custom" selected>custom (General Plugins Snippets & Guides)</option>
                    </select>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #a7aaad;">Reference Source Text Buffer Material:</label>
                    <textarea id="ts-rag-text" rows="10" placeholder="Paste framework document references, class models, or hook specification definitions line-by-line here..." required style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 10px; border-radius: 4px; box-sizing: border-box; font-family: monospace; font-size: 12px; line-height: 1.4; resize: none;"></textarea>
                </div>
                <button type="submit" id="ts-rag-submit-btn" class="button button-primary" style="width: 100%; font-weight: bold; height: 38px;">Commit Snippet to Long-Term Memory</button>
            </form>
            <div id="ts-rag-form-feedback" style="margin-top: 14px; font-size: 13px; font-weight: bold; text-align: center;"></div>
        </div>

        
        <div style="flex: 2; min-width: 480px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e;">
            <h3 style="color: #fff; margin-top: 0; margin-bottom: 16px;">🗂️ Indexed Semantic Memory Snippets</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-family: monospace; font-size: 13px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #3c3c43; color: #a7aaad;">
                            <th style="padding: 12px 8px;">Tag</th>
                            <th style="padding: 12px 8px;">Matching Query Keywords</th>
                            <th style="padding: 12px 8px;">Snippet Text Summary</th>
                            <th style="padding: 12px 8px; text-align: right; width: 100px;">Control</th>
                        </tr>
                    </thead>
                    <tbody id="ts-rag-memory-table-body">
                        <tr><td colspan="4" style="padding: 20px 8px; color: #a7aaad; font-style: italic;">Synchronizing semantic memory clusters...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const S = window.TERSOSTUDIO_State || {};
    const restUrl = S.rest_url || '';
    const nonce = S.nonce || '';

    const form = document.getElementById('ts-rag-ingestion-form');
    const submitBtn = document.getElementById('ts-rag-submit-btn');
    const feedback = document.getElementById('ts-rag-form-feedback');
    const tableBody = document.getElementById('ts-rag-memory-table-body');

    function fetchKnowledgeIndex() {
        fetch(`${restUrl}/chat/knowledge`, { headers: { 'X-WP-Nonce': nonce } })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data && data.data.records) {
                const rows = data.data.records;
                if (rows.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" style="padding: 20px 8px; color: #a7aaad; font-style: italic;">The long-term knowledge base memory ledger is currently empty. Add parameters on the left.</td></tr>';
                    return;
                }
                tableBody.innerHTML = '';
                rows.forEach(record => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #29292e';
                    
                    let truncatedText = record.reference_text.replace(/<[^>]*>/g, '');
                    if (truncatedText.length > 60) truncatedText = truncatedText.substring(0, 57) + '...';

                    let tagColor = '#eab308';
                    if (record.context_tag === 'wp_core') tagColor = '#10b981';
                    if (record.context_tag === 'blueprint') tagColor = '#2563eb';

                    tr.innerHTML = `
                        <td style="padding: 12px 8px;"><span style="color: ${tagColor}; font-weight: bold;">[${record.context_tag}]</span></td>
                        <td style="padding: 12px 8px; color: #fff; font-weight: bold;">${record.lookup_keyword}</td>
                        <td style="padding: 12px 8px; color: #a7aaad;">${truncatedText}</td>
                        <td style="padding: 12px 8px; text-align: right;">
                            <span class="ts-rag-delete-link" data-id="${record.id}" style="color: #f43f5e; cursor: pointer; text-decoration: underline;">Purge</span>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });

                document.querySelectorAll('.ts-rag-delete-link').forEach(link => {
                    link.addEventListener('click', function() {
                        const rId = this.getAttribute('data-id');
                        if (!confirm('Purge snippet entry from long-term agent memory maps?')) return;
                        
                        fetch(`${restUrl}/chat/knowledge/delete`, {
                            method: 'POST',
                            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: parseInt(rId, 10) })
                        })
                        .then(res => res.json())
                        .then(resData => {
                            if (resData.success) fetchKnowledgeIndex();
                        });
                    });
                });
            }
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Injecting memory...';
        feedback.textContent = '';

        const payload = {
            keyword: document.getElementById('ts-rag-keyword').value.trim(),
            tag: document.getElementById('ts-rag-tag').value,
            reference: document.getElementById('ts-rag-text').value.trim()
        };

        fetch(`${restUrl}/chat/knowledge/add`, {
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Commit Snippet to Long-Term Memory';
            if (data.success) {
                feedback.textContent = '✓ Snippet successfully integrated.';
                feedback.style.color = '#10b981';
                form.reset();
                fetchKnowledgeIndex();
            } else {
                feedback.textContent = `Error: ${data.message}`;
                feedback.style.color = '#f43f5e';
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Commit Snippet to Long-Term Memory';
            feedback.textContent = `Connection crash: ${err.message}`;
            feedback.style.color = '#f43f5e';
        });
    });

    fetchKnowledgeIndex();
});
</script>
