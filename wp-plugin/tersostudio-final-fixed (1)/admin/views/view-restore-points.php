<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Git-Like State Manager // Restore Points Dashboard</h1>
    
    
    <div style="background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e; margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 24px;">
        <div style="flex: 1; min-width: 260px;">
            <h3 style="color:#fff; margin-top:0;">🧹 Automated Retention Policy Guard</h3>
            <div style="display:flex; gap:16px; margin-top:12px;">
                <div>
                    <label style="display:block; font-size:11px; color:#a7aaad; margin-bottom:4px;">Max Snapshots to Keep</label>
                    <input type="number" id="ts-policy-max" value="20" style="width:100px; background:#2a2a2e; color:#fff; border:1px solid #3c3c43; padding:6px; border-radius:4px;" />
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:#a7aaad; margin-bottom:4px;">Auto-Delete Age Limit (Days)</label>
                    <input type="number" id="ts-policy-days" value="30" style="width:100px; background:#2a2a2e; color:#fff; border:1px solid #3c3c43; padding:6px; border-radius:4px;" />
                </div>
            </div>
            <button id="ts-save-policy-btn" class="button" style="margin-top:12px; font-weight:bold; background:#10b981; color:#fff; border:none;">Update Retention Rules</button>
        </div>
    </div>

    <div style="background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="color: #fff; margin-top: 0; margin-bottom: 12px;">⏱️ Workspace Snapshots Control</h2>
            <label style="font-weight: 600; margin-right: 10px; color: #a7aaad; font-size: 13px;">Active Project Scope:</label>
            <select id="ts-restore-project-select" style="background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 6px; border-radius: 4px; min-width: 240px;">
                <option value="0">-- Select Project Workspace Context --</option>
            </select>
        </div>
        
        <div id="ts-create-snap-box" style="display: none; align-items: center; flex-wrap: nowrap; gap: 12px; height: 40px; margin-top: 20px;">
            <input type="text" id="ts-snap-input-name" placeholder="Enter milestone name (e.g. before prompt rewrite)..." style="width: 320px; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px 12px; border-radius: 4px; height: 100%; box-sizing: border-box; line-height: 1; vertical-align: middle;" />
            <button id="ts-trigger-snap-create" class="button button-primary" style="font-weight: bold; height: 100%; display: inline-flex; align-items: center; justify-content: center; margin: 0; padding: 0 16px; line-height: 1; vertical-align: middle;">Capture Present Snapshot</button>
        </div>
    </div>
    <div id="ts-restore-global-msg" style="margin: 12px 0; font-size: 14px; font-weight: bold;"></div>

    <div style="background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e;">
        <h3 style="color: #fff; margin-top: 0; margin-bottom: 20px;">Timeline Snapshot Records</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #3c3c43;">
                        <th style="padding: 12px 8px; color: #a7aaad;">Snapshot Index</th>
                        <th style="padding: 12px 8px; color: #a7aaad;">Milestone Description Label</th>
                        <th style="padding: 12px 8px; color: #a7aaad;">Archive Storage Folder Token</th>
                        <th style="padding: 12px 8px; color: #a7aaad;">Created At Timestamp</th>
                        <th style="padding: 12px 8px; color: #a7aaad; width: 240px;">Operational Actions</th>
                    </tr>
                </thead>
                <tbody id="ts-restore-table-body">
                    <tr><td colspan="5" style="padding: 16px 8px; color: #a7aaad; font-style: italic;">Select an active repository context token from the dropdown select scope.</td></tr>
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

    const projSelect = document.getElementById('ts-restore-project-select');
    const createBox = document.getElementById('ts-create-snap-box');
    const createBtn = document.getElementById('ts-trigger-snap-create');
    const snapNameInput = document.getElementById('ts-snap-input-name');
    const tableBody = document.getElementById('ts-restore-table-body');
    const globalMsg = document.getElementById('ts-restore-global-msg');
    
    const policyMax = document.getElementById('ts-policy-max');
    const policyDays = document.getElementById('ts-policy-days');
    const savePolicyBtn = document.getElementById('ts-save-policy-btn');

    function renderMessage(text, isError = false) {
        globalMsg.textContent = text;
        globalMsg.style.color = isError ? '#ff4d4f' : '#10b981';
    }

    fetch(restUrl + '/projects', { headers: { 'X-WP-Nonce': nonce } })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.data && data.data.projects) {
            data.data.projects.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.name} [slug: ${p.slug}]`;
                projSelect.appendChild(opt);
            });
        }
    });

    if (savePolicyBtn) {
        savePolicyBtn.addEventListener('click', function() {
            savePolicyBtn.disabled = true;
            fetch(restUrl + '/restore', {
                method: 'POST',
                headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    project_id: 1,
                    action_type: 'save_policy',
                    policy_max: parseInt(policyMax.value, 10),
                    policy_days: parseInt(policyDays.value, 10)
                })
            })
            .then(res => res.json())
            .then(data => {
                savePolicyBtn.disabled = false;
                if (data.success) {
                    renderMessage('Ecosystem automatic storage threshold policies updated.');
                }
            });
        });
    }

    function rehydrateSnapshotsTable() {
        const pId = intval(projSelect.value);
        if (pId === 0) {
            createBox.style.display = 'none';
            tableBody.innerHTML = '<tr><td colspan="5" style="padding: 16px 8px; color: #a7aaad; font-style: italic;">Select an active repository context token from the dropdown select scope.</td></tr>';
            return;
        }

        createBox.style.display = 'inline-flex';
        tableBody.innerHTML = '<tr><td colspan="5" style="padding: 16px 8px; color: #eab308; font-style: italic;">Loading history timelines...</td></tr>';

        const paramConnector = restUrl.indexOf('?') !== -1 ? '&' : '?';
        const requestEndpointUrl = restUrl + '/restore' + paramConnector + 'project_id=' + pId;

        fetch(requestEndpointUrl, { headers: { 'X-WP-Nonce': nonce } })
        .then(res => {
            if (!res.ok) throw new Error(`Server returned HTTP code matrix fault: ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.success && data.data && data.data.snapshots) {
                policyMax.value = data.data.policy_max || 20;
                policyDays.value = data.data.policy_days || 30;
                
                const snaps = data.data.snapshots;
                tableBody.innerHTML = '';
                if (snaps.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" style="padding: 16px 8px; color: #a7aaad; font-style: italic;">No snapshots recorded for this project scope. Create one above to lock current states.</td></tr>';
                    return;
                }
                snaps.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #29292e';
                    tr.innerHTML = `
                        <td style="padding: 12px 8px; color: #eab308;">#SNAP_${row.id}</td>
                        <td style="padding: 12px 8px; font-weight: 600;">${row.snapshot_name}</td>
                        <td style="padding: 12px 8px; color: #a7aaad; font-family: monospace;">${row.snapshot_path}</td>
                        <td style="padding: 12px 8px; color: #a7aaad;">${row.created_at}</td>
                        <td style="padding: 12px 8px; display: flex; gap: 8px;">
                            <button class="button ts-rollback-trigger" data-slug="${row.snapshot_path}" style="background: #2563eb; color: #fff; border: none; font-weight: bold;">
                                <span class="dashicons dashicons-backup" style="line-height: 1.5; margin-right: 4px;"></span>Rollback
                            </button>
                            <button class="button ts-delete-trigger" data-slug="${row.snapshot_path}" style="background: #dc2626; color: #fff; border: none; font-weight: bold;">
                                <span class="dashicons dashicons-trash" style="line-height: 1.5; margin-right: 4px;"></span>Delete
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });

                document.querySelectorAll('.ts-rollback-trigger').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const slug = this.getAttribute('data-slug');
                        if (!confirm('Execute rollback? Current code modifications will be overridden.')) return;
                        
                        renderMessage('Initiating roll-back sequence against storage disk fields...');
                        fetch(restUrl + '/restore', {
                            method: 'POST',
                            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
                            body: JSON.stringify({ project_id: pId, action_type: 'restore', snapshot_slug: slug })
                        })
                        .then(res => res.json())
                        .then(resData => {
                            if (resData.success) {
                                renderMessage('Success: Target workspace timeline state context restored cleanly!');
                                rehydrateSnapshotsTable();
                            }
                        });
                    });
                });

                document.querySelectorAll('.ts-delete-trigger').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const slug = this.getAttribute('data-slug');
                        if (!confirm('Are you absolutely sure you want to permanently delete this snapshot block from disk storage? This is non-reversible.')) return;
                        
                        renderMessage('Purging snapshot archive structure from storage layer arrays...');
                        fetch(restUrl + '/restore', {
                            method: 'POST',
                            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
                            body: JSON.stringify({ project_id: pId, action_type: 'delete', snapshot_slug: slug })
                        })
                        .then(res => res.json())
                        .then(resData => {
                            if (resData.success) {
                                renderMessage('Snapshot permanently purged.');
                                rehydrateSnapshotsTable();
                            }
                        });
                    });
                });
            } else {
                tableBody.innerHTML = `<tr><td colspan="5" style="padding: 16px 8px; color: #ff4d4f; font-weight: bold;">Operational Rejection: ${data.message || 'Malformed dataset container.'}</td></tr>`;
            }
        })
        .catch(err => {
            tableBody.innerHTML = `<tr><td colspan="5" style="padding: 16px 8px; color: #ff4d4f; font-weight: bold;">Connectivity Exception: ${err.message}</td></tr>`;
        });
    }

    function intval(val) {
        const parsed = parseInt(val, 10);
        return isNaN(parsed) ? 0 : parsed;
    }

    projSelect.addEventListener('change', rehydrateSnapshotsTable);

    if (createBtn) {
        createBtn.addEventListener('click', function() {
            const pId = intval(projSelect.value);
            const name = snapNameInput.value.trim();
            
            createBtn.disabled = true;
            createBtn.textContent = 'Freezing state...';
            renderMessage('Iterating code stream elements over filesystem_gate layer arrays...');

            fetch(restUrl + '/restore', {
                method: 'POST',
                headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
                body: JSON.stringify({ project_id: pId, action_type: 'create', snapshot_name: name })
            })
            .then(res => res.json())
            .then(data => {
                createBtn.disabled = false;
                createBtn.textContent = 'Capture Present Snapshot';
                if (data.success) {
                    renderMessage('Snapshot frozen and tracked successfully.');
                    snapNameInput.value = '';
                    rehydrateSnapshotsTable();
                } else {
                    renderMessage(`Archival block error: ${data.message}`, true);
                }
            })
            .catch(err => {
                createBtn.disabled = false;
                createBtn.textContent = 'Capture Present Snapshot';
                renderMessage('Network communication fault locking state buffers.', true);
            });
        });
    }
});
</script>
