<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Workspace Projects Registry</h1>
    
    <div style="display: flex; gap: 24px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 340px; max-width: 420px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e;">
            <h2 style="color: #fff; margin-top: 0;">Initialize New Workspace</h2>
            <p style="color: #a7aaad; font-size: 13px;">Establish a clean repository state database context before launching the IDE Swarm.</p>
            <form id="ts-create-project-form" style="margin-top: 20px;">
                <div style="margin-bottom: 14px;">
                    <label for="ts-proj-name" style="display: block; margin-bottom: 6px; font-weight: 600;">Project Name</label>
                    <input type="text" id="ts-proj-name" placeholder="e.g., Advanced Custom Hooks" required style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px; box-sizing: border-box;" />
                </div>
                <div style="margin-bottom: 14px;">
                    <label for="ts-proj-slug" style="display: block; margin-bottom: 6px; font-weight: 600; color: #a7aaad;">Repository Slug (Optional)</label>
                    <input type="text" id="ts-proj-slug" placeholder="Auto-generated if left blank..." style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px; box-sizing: border-box;" />
                </div>
                <div style="margin-bottom: 14px;">
                    <label for="ts-proj-cat" style="display: block; margin-bottom: 6px; font-weight: 600; color: #eab308;">Select Workspace Category</label>
                    <select id="ts-proj-cat" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;">
                        <option value="uncategorized">Loading system categories...</option>
                    </select>
                </div>
                <div style="margin-bottom: 14px; border-top: 1px solid #29292e; padding-top: 14px;">
                    <label for="ts-proj-import" style="display: block; margin-bottom: 6px; font-weight: 600; color: #60a5fa;">📥 Optional: Ingest Local Directory Plugin</label>
                    <select id="ts-proj-import" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 8px; border-radius: 4px;">
                        <option value=".">-- Create Blank Extension Shell --</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 22px; border-top: 1px solid #29292e; padding-top: 14px;">
                    <label for="ts-proj-zip" style="display: block; margin-bottom: 6px; font-weight: 600; color: #34d399;">📦 Alternative: Upload Extension package (.ZIP)</label>
                    <input type="file" id="ts-proj-zip" accept=".zip" style="width: 100%; color: #fff; background: #2a2a2e; padding: 6px; border-radius: 4px; border: 1px solid #3c3c43; box-sizing: border-box;" />
                    <span style="font-size: 11px; color: #a7aaad; display: block; margin-top: 6px;">Upload a compressed workspace ZIP. The pipeline bypasses media assets, vendor frameworks, or stylesheets automatically.</span>
                </div>
                <button type="submit" id="ts-proj-submit" class="button button-primary" style="width: 100%; text-align: center; justify-content: center; font-weight: bold; height: 38px;">Provision Workspace State</button>
            </form>
            <div id="ts-proj-msg" style="margin-top: 16px; font-size: 13px; font-weight: 600;"></div>
        </div>

        <div style="flex: 2; min-width: 500px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e;">
            <h2 style="color: #fff; margin-top: 0;">Active Repositories</h2>
            <div style="overflow-x: auto; margin-top: 20px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #3c3c43;">
                            <th style="padding: 12px 8px; color: #a7aaad;">ID</th>
                            <th style="padding: 12px 8px; color: #a7aaad;">Name</th>
                            <th style="padding: 12px 8px; color: #a7aaad;">Category</th>
                            <th style="padding: 12px 8px; color: #a7aaad;">Directory Slug</th>
                            <th style="padding: 12px 8px; color: #a7aaad; width: 220px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="ts-projects-list">
                        <tr><td colspan="5" style="padding: 16px 8px; color: #a7aaad; font-style: italic;">Synchronizing with state repository engine...</td></tr>
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

    const msgBox = document.getElementById('ts-proj-msg');
    const form = document.getElementById('ts-create-project-form');
    const submitBtn = document.getElementById('ts-proj-submit');
    const listBody = document.getElementById('ts-projects-list');
    const catSelect = document.getElementById('ts-proj-cat');
    const importSelect = document.getElementById('ts-proj-import');
    const zipInput = document.getElementById('ts-proj-zip');

    function displayMessage(msg, isError = false) {
        msgBox.textContent = msg;
        msgBox.style.color = isError ? '#ff4d4f' : '#10b981';
    }

    function fetchProjects() {
        if (!restUrl) return;
        fetch(restUrl + '/projects', {
            method: 'GET',
            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                if (data.data.categories) {
                    catSelect.innerHTML = '';
                    data.data.categories.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.slug;
                        opt.textContent = c.name;
                        catSelect.appendChild(opt);
                    });
                }

                if (data.data.plugins) {
                    importSelect.innerHTML = '<option value=".">-- Create Blank Extension Shell --</option>';
                    data.data.plugins.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.folder !== '.' ? p.folder : p.path;
                        opt.textContent = p.name + ` [plugins/${p.path}]`;
                        importSelect.appendChild(opt);
                    });
                }

                const projects = data.data.projects || [];
                listBody.innerHTML = '';
                if (projects.length === 0) {
                    listBody.innerHTML = '<tr><td colspan="5" style="padding: 16px 8px; color: #a7aaad; font-style: italic;">No active workspaces detected.</td></tr>';
                    return;
                }
                projects.forEach(proj => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #29292e';
                    tr.innerHTML = `
                        <td style="padding: 12px 8px;">#${proj.id}</td>
                        <td style="padding: 12px 8px; font-weight: 600;">${proj.name}</td>
                        <td style="padding: 12px 8px; color: #eab308;">${proj.category_slug}</td>
                        <td style="padding: 12px 8px; color: #a7aaad; font-family: monospace;">${proj.slug}</td>
                        <td style="padding: 12px 8px; display: flex; gap: 6px;">
                            <a href="?page=tersostudio-workbench&project_id=${proj.id}" class="button" style="background: #2271b1; color: #fff; border: none; font-weight: bold;">
                                <span class="dashicons dashicons-admin-customizer" style="line-height: 1.5; margin-right: 4px;"></span>Launch IDE
                            </a>
                            <button class="button ts-project-delete-trigger" data-id="${proj.id}" data-name="${proj.name}" style="background: #dc2626; color: #fff; border: none; font-weight: bold; font-family: monospace;">❌ Delete</button>
                        </td>
                    `;
                    listBody.appendChild(tr);
                });

                document.querySelectorAll('.ts-project-delete-trigger').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const pId = parseInt(this.getAttribute('data-id'), 10);
                        const pName = this.getAttribute('data-name');
                        if (!confirm(`Are you completely sure you want to delete "${pName}"? This wipes database code records and active disk sandbox pathways permanently.`)) return;

                        this.disabled = true;
                        this.textContent = 'Purging...';
                        fetch(`${restUrl}/projects/delete`, {
                            method: 'POST',
                            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
                            body: JSON.stringify({ project_id: pId })
                        })
                        .then(res => res.json())
                        .then(resData => {
                            if (resData.success) {
                                fetchProjects();
                            } else {
                                this.disabled = false;
                                this.textContent = '❌ Delete';
                            }
                        });
                    });
                });
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('ts-proj-name').value.trim();
            const slug = document.getElementById('ts-proj-slug').value.trim();
            const cat = catSelect.value;
            const imp = importSelect.value;
            const zipFile = zipInput.files[0];

            submitBtn.disabled = true;
            submitBtn.textContent = 'Provisioning...';
            displayMessage('Transmitting workspace variables over security boundaries...');

            // Dynamic Fork Route Selection: Routes data to upload channels if file binaries are loaded
            if (zipFile) {
                const fd = new FormData();
                fd.append('name', name);
                fd.append('slug', slug);
                fd.append('category_slug', cat);
                fd.append('plugin_zip', zipFile);

                fetch(`${restUrl}/projects/upload`, {
                    method: 'POST',
                    headers: { 'X-WP-Nonce': nonce },
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Provision Workspace State';
                    if (data.success) {
                        displayMessage('Success: Package decompressed, noise files skipped, and core architectural components indexed!');
                        form.reset();
                        fetchProjects();
                    } else {
                        displayMessage(`Upload Fault: ${data.message}`, true);
                    }
                });
            } else {
                fetch(restUrl + '/projects', {
                    method: 'POST',
                    headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name: name, slug: slug, category_slug: cat, source_plugin: imp })
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Provision Workspace State';
                    if (data.success) {
                        displayMessage('Workspace layout successfully provisioned.');
                        form.reset();
                        fetchProjects();
                    } else {
                        displayMessage(`API Error: ${data.message}`, true);
                    }
                });
            }
        });
    }

    fetchProjects();
});
</script>
