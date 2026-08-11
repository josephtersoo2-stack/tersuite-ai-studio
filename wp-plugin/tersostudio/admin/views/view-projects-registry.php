<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap" style="max-width: 1200px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="display: flex; justify-content: space-between; items-center; margin-bottom: 24px;">
        <h1 style="color: #0f172a; font-weight: 800; font-size: 26px; margin: 0; display: flex; align-items: center; gap: 10px;">
            ⚡ Workspace Projects & Categories Registry
        </h1>
        <button id="ts-open-cat-modal" class="button" style="background: #4f46e5; color: #fff; border: none; font-weight: bold; padding: 6px 16px; border-radius: 6px; cursor: pointer;">
            ⚙️ Manage Categories
        </button>
    </div>
    
    <div style="display: flex; gap: 24px; flex-wrap: wrap;">
        <!-- Left: Create Project Panel -->
        <div style="flex: 1; min-width: 360px; max-width: 440px; background: #0f172a; color: #f8fafc; padding: 24px; border-radius: 12px; border: 1px solid #1e293b; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
            <h2 style="color: #fff; margin-top: 0; font-size: 18px; font-weight: 700; border-b: 1px solid #1e293b; padding-bottom: 12px;">
                ➕ Create New Plugin Project
            </h2>
            <p style="color: #94a3b8; font-size: 12px; line-height: 1.5; margin-bottom: 20px;">
                Initialize a project shell in WordPress & dispatch the CrewAI backend orchestrator.
            </p>
            
            <form id="ts-create-project-form">
                <div style="margin-bottom: 16px;">
                    <label for="ts-proj-name" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #cbd5e1;">Plugin Title *</label>
                    <input type="text" id="ts-proj-name" placeholder="e.g. WooCommerce Custom Discount Matrix" required style="width: 100%; background: #020617; color: #fff; border: 1px solid #334155; padding: 10px; border-radius: 8px; box-sizing: border-box; font-size: 13px;" />
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="ts-proj-desc" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #cbd5e1;">Requirements & Features *</label>
                    <textarea id="ts-proj-desc" rows="3" placeholder="Describe shortcodes, admin settings, hooks, or features..." required style="width: 100%; background: #020617; color: #fff; border: 1px solid #334155; padding: 10px; border-radius: 8px; box-sizing: border-box; font-size: 13px;"></textarea>
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="ts-proj-cat" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #f59e0b;">Plugin Category</label>
                    <select id="ts-proj-cat" style="width: 100%; background: #020617; color: #fff; border: 1px solid #334155; padding: 10px; border-radius: 8px; font-size: 13px;">
                        <option value="">Loading system categories...</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="ts-proj-model" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #38bdf8;">AI Model Provider</label>
                    <select id="ts-proj-model" style="width: 100%; background: #020617; color: #38bdf8; font-weight: 600; border: 1px solid #334155; padding: 10px; border-radius: 8px; font-size: 13px;">
                        <option value="gemini-3.6-flash">Gemini 3.6 Flash (Fastest, High Performance)</option>
                        <option value="gemini-3.5-flash">Gemini 3.5 Flash</option>
                        <option value="gemini-3.1-pro-preview">Gemini 3.1 Pro Preview (Frontier Logic)</option>
                    </select>
                </div>

                <button type="submit" id="ts-proj-submit" class="button button-primary" style="width: 100%; text-align: center; justify-content: center; font-weight: bold; height: 42px; background: #4f46e5; border: none; border-radius: 8px; font-size: 14px; cursor: pointer;">
                    🚀 Create & Dispatch AI Pipeline
                </button>
            </form>
            <div id="ts-proj-msg" style="margin-top: 16px; font-size: 13px; font-weight: 600;"></div>
        </div>

        <!-- Right: Projects Table List -->
        <div style="flex: 2; min-width: 500px; background: #0f172a; color: #f8fafc; padding: 24px; border-radius: 12px; border: 1px solid #1e293b; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="color: #fff; margin: 0; font-size: 18px; font-weight: 700;">Active Projects</h2>
                <button id="ts-refresh-btn" class="button" style="background: #1e293b; color: #cbd5e1; border: 1px solid #334155; border-radius: 6px; padding: 4px 12px; font-size: 12px;">
                    🔄 Refresh
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #334155;">
                            <th style="padding: 12px 8px; color: #94a3b8; font-size: 12px;">ID</th>
                            <th style="padding: 12px 8px; color: #94a3b8; font-size: 12px;">Plugin Name</th>
                            <th style="padding: 12px 8px; color: #94a3b8; font-size: 12px;">Category</th>
                            <th style="padding: 12px 8px; color: #94a3b8; font-size: 12px;">Status</th>
                            <th style="padding: 12px 8px; color: #94a3b8; font-size: 12px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="ts-projects-list">
                        <tr><td colspan="5" style="padding: 20px; color: #94a3b8; font-style: italic; text-align: center;">Loading project registry...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- CATEGORY MANAGEMENT MODAL -->
<div id="ts-cat-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); z-index: 99999; justify-content: center; align-items: center;">
    <div style="background: #0f172a; color: #fff; width: 90%; max-width: 650px; border-radius: 14px; border: 1px solid #334155; padding: 28px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-b: 1px solid #1e293b; pb: 12px;">
            <h3 style="margin: 0; color: #fff; font-size: 20px; font-weight: 800;">🏷️ Category Manager</h3>
            <button id="ts-close-cat-modal" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <!-- Add New Category Form -->
        <form id="ts-add-cat-form" style="display: flex; gap: 10px; margin-bottom: 24px; background: #020617; padding: 14px; border-radius: 10px; border: 1px solid #1e293b;">
            <input type="text" id="ts-new-cat-name" placeholder="New Category Name (e.g. SEO & Schema)" required style="flex: 2; background: #0f172a; color: #fff; border: 1px solid #334155; padding: 8px 12px; border-radius: 6px; font-size: 13px;" />
            <input type="color" id="ts-new-cat-color" value="#6366f1" style="width: 40px; height: 36px; border: none; background: none; cursor: pointer;" />
            <button type="submit" class="button" style="background: #10b981; color: #fff; border: none; font-weight: bold; padding: 0 16px; border-radius: 6px; cursor: pointer;">
                + Add Category
            </button>
        </form>

        <!-- Categories List -->
        <div style="max-height: 300px; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid #334155;">
                        <th style="padding: 8px; color: #94a3b8; font-size: 12px;">Category Name</th>
                        <th style="padding: 8px; color: #94a3b8; font-size: 12px;">Slug</th>
                        <th style="padding: 8px; color: #94a3b8; font-size: 12px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody id="ts-cats-table-body">
                    <tr><td colspan="3" style="padding: 12px; text-align: center; color: #94a3b8;">Loading categories...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const S = window.TERSOSTUDIO_State || {};
    const restUrl = S.rest_url || '/wp-json/tersostudio/v2';
    const nonce = S.nonce || '';

    const msgBox = document.getElementById('ts-proj-msg');
    const form = document.getElementById('ts-create-project-form');
    const submitBtn = document.getElementById('ts-proj-submit');
    const listBody = document.getElementById('ts-projects-list');
    const catSelect = document.getElementById('ts-proj-cat');

    // Modal elements
    const catModal = document.getElementById('ts-cat-modal');
    const openCatBtn = document.getElementById('ts-open-cat-modal');
    const closeCatBtn = document.getElementById('ts-close-cat-modal');
    const addCatForm = document.getElementById('ts-add-cat-form');
    const catsTableBody = document.getElementById('ts-cats-table-body');

    openCatBtn.addEventListener('click', () => { catModal.style.display = 'flex'; loadCategories(); });
    closeCatBtn.addEventListener('click', () => { catModal.style.display = 'none'; });

    function displayMessage(msg, isError = false) {
        msgBox.textContent = msg;
        msgBox.style.color = isError ? '#ef4444' : '#10b981';
    }

    function loadCategories() {
        fetch(restUrl + '/categories', { headers: { 'X-WP-Nonce': nonce } })
        .then(res => res.json())
        .then(data => {
            const categories = data.results || data.categories || data || [];
            catSelect.innerHTML = '<option value="">-- Select Category --</option>';
            catsTableBody.innerHTML = '';

            categories.forEach(c => {
                // Populate dropdown
                const opt = document.createElement('option');
                opt.value = c.id || c.slug;
                opt.textContent = c.name;
                catSelect.appendChild(opt);

                // Populate modal table
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #1e293b';
                tr.innerHTML = `
                    <td style="padding: 10px 8px; font-weight: 600;">
                        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:${c.color || '#6366f1'}; margin-right:8px;"></span>
                        ${c.name}
                    </td>
                    <td style="padding: 10px 8px; color: #94a3b8; font-family: monospace; font-size: 11px;">${c.slug}</td>
                    <td style="padding: 10px 8px; text-align: right;">
                        <button class="button ts-delete-cat-btn" data-id="${c.id}" style="background:#ef4444; color:#fff; border:none; font-size:11px; padding:2px 8px; border-radius:4px; cursor:pointer;">Delete</button>
                    </td>
                `;
                catsTableBody.appendChild(tr);
            });

            document.querySelectorAll('.ts-delete-cat-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const catId = this.getAttribute('data-id');
                    if (!confirm('Delete this category?')) return;
                    fetch(`${restUrl}/categories/${catId}`, {
                        method: 'DELETE',
                        headers: { 'X-WP-Nonce': nonce }
                    }).then(() => loadCategories());
                });
            });
        });
    }

    function fetchProjects() {
        fetch(restUrl + '/projects', { headers: { 'X-WP-Nonce': nonce } })
        .then(res => res.json())
        .then(data => {
            const projects = data.projects || data.results || data || [];
            listBody.innerHTML = '';
            if (projects.length === 0) {
                listBody.innerHTML = '<tr><td colspan="5" style="padding: 20px; color: #94a3b8; text-align: center;">No projects created yet.</td></tr>';
                return;
            }
            projects.forEach(p => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #1e293b';
                tr.innerHTML = `
                    <td style="padding: 12px 8px; font-family: monospace; font-size: 11px; color: #94a3b8;">#${(p.id || '').substring(0, 8)}...</td>
                    <td style="padding: 12px 8px; font-weight: 700; color: #fff;">${p.name}</td>
                    <td style="padding: 12px 8px; color: #f59e0b; font-size: 12px;">${p.category?.name || 'General'}</td>
                    <td style="padding: 12px 8px;"><span style="background: rgba(16,185,129,0.1); color: #10b981; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">${p.status || 'Active'}</span></td>
                    <td style="padding: 12px 8px; text-align: right;">
                        <a href="?page=tersostudio-workbench&project_id=${p.id}" class="button" style="background: #4f46e5; color: #fff; border: none; font-size: 11px; font-weight: bold; border-radius: 6px; padding: 4px 10px;">Launch IDE ↗</a>
                        <button class="button ts-delete-proj-btn" data-id="${p.id}" style="background: #dc2626; color: #fff; border: none; font-size: 11px; font-weight: bold; border-radius: 6px; padding: 4px 10px; margin-left: 4px;">Delete</button>
                    </td>
                `;
                listBody.appendChild(tr);
            });

            document.querySelectorAll('.ts-delete-proj-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const pId = this.getAttribute('data-id');
                    if (!confirm('Delete this project?')) return;
                    fetch(`${restUrl}/projects/${pId}`, {
                        method: 'DELETE',
                        headers: { 'X-WP-Nonce': nonce }
                    }).then(() => fetchProjects());
                });
            });
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('ts-proj-name').value.trim();
            const desc = document.getElementById('ts-proj-desc').value.trim();
            const catId = catSelect.value;
            const model = document.getElementById('ts-proj-model').value;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating Project & Dispatching Agents...';
            displayMessage('Transmitting project specifications to backend...');

            fetch(restUrl + '/projects', {
                method: 'POST',
                headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name, description: desc, category_id: catId, metadata: { model: model } })
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = '🚀 Create & Dispatch AI Pipeline';
                if (data.success || data.id) {
                    displayMessage('Project created successfully!');
                    form.reset();
                    fetchProjects();
                } else {
                    displayMessage(`Error: ${data.message || data.error}`, true);
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.textContent = '🚀 Create & Dispatch AI Pipeline';
                displayMessage(`Connection Error: ${err.message}`, true);
            });
        });
    }

    if (addCatForm) {
        addCatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('ts-new-cat-name').value.trim();
            const color = document.getElementById('ts-new-cat-color').value;

            fetch(restUrl + '/categories', {
                method: 'POST',
                headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name, color: color })
            })
            .then(() => {
                document.getElementById('ts-new-cat-name').value = '';
                loadCategories();
            });
        });
    }

    document.getElementById('ts-refresh-btn').addEventListener('click', fetchProjects);

    loadCategories();
    fetchProjects();
});
</script>
