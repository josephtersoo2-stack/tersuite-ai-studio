<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $wpdb;

// Enforce Strict SQL Prepare Rule on view query statements blocks
$query = $wpdb->prepare( "SELECT id, name, slug FROM {$wpdb->prefix}ts_projects ORDER BY id DESC", [] );
$projects = $wpdb->get_results( $query, ARRAY_A ) ?: [];
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Ecosystem Continuous Deployment Pipeline Panel</h1>
    
    <div style="background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e; max-width: 720px;">
        <h2 style="color: #fff; margin-top: 0; margin-bottom: 16px;">🚀 Bridge Sandboxed Builds to Live Site Paths</h2>
        <p style="color: #a7aaad; font-size: 13px; line-height: 1.5; margin-bottom: 24px;">
            Select a development project below. This deployment utility extracts the latest self-healed workspace code from our database parameters and pushes the clean folder structure directly into your active server directory path (<code>wp-content/plugins/</code>).
        </p>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #60a5fa;">Target Deployment Context Plugin Scope:</label>
            <select id="ts-deploy-project-select" style="width: 100%; background: #2a2a2e; color: #fff; border: 1px solid #3c3c43; padding: 10px; border-radius: 4px; font-family: monospace;">
                <option value="0">-- Select Sandboxed Project to Deploy --</option>
                <?php foreach ( $projects as $p ) : ?>
                    <option value="<?php echo intval( $p['id'] ); ?>" data-slug="<?php echo esc_attr( $p['slug'] ); ?>">
                        <?php echo esc_html( $p['name'] ); ?> [directory: /wp-content/plugins/<?php echo esc_html( $p['slug'] ); ?>/]
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="ts-deploy-status-box" style="display: none; margin: 20px 0; padding: 14px; border-radius: 6px; background: #16161a; font-family: monospace; font-size: 12px; border: 1px solid #29292e; color: #eab308;">
            READY_VECTOR -> Path verification complete. Standby for delivery clearance initialization.
        </div>

        <div style="margin-top: 32px;">
            <button id="ts-trigger-deploy-btn" class="button button-primary" style="height: 44px; padding: 0 24px; font-weight: bold; font-size: 14px; background: #2563eb; border: none; text-shadow: none;" disabled>Deploy Extension to Production ⚡</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const S = window.TERSOSTUDIO_State || {};
    const restUrl = S.rest_url || '';
    const nonce = S.nonce || '';

    const projSelect = document.getElementById('ts-deploy-project-select');
    const deployBtn = document.getElementById('ts-trigger-deploy-btn');
    const statusBox = document.getElementById('ts-deploy-status-box');

    projSelect.addEventListener('change', function() {
        const pId = parseInt(this.value, 10);
        if (pId === 0) {
            deployBtn.disabled = true;
            statusBox.style.display = 'none';
        } else {
            deployBtn.disabled = false;
            statusBox.style.display = 'block';
            const activeOpt = this.options[this.selectedIndex];
            statusBox.textContent = `TARGET_PATH -> wp-content/plugins/${activeOpt.getAttribute('data-slug')}/ \nSTATUS -> Standby for deployment parameters authorization.`;
            statusBox.style.color = '#eab308';
            statusBox.style.borderColor = '#29292e';
        }
    });

    deployBtn.addEventListener('click', function() {
        const pId = parseInt(projSelect.value, 10);
        if (pId === 0) return;

        if (!confirm('CONFIRM LIVE PRODUCTION UPDATE: This action moves your workspace plugin files directly into the active system directories. Execute build delivery pipeline?')) return;

        deployBtn.disabled = true;
        deployBtn.textContent = 'Pulsing Code Arrays...';
        statusBox.textContent = 'ROUTING_PIPELINE -> Sweeping local staging layers and writing clean code structures to system disk channels...';
        statusBox.style.color = '#38bdf8';

        fetch(`${restUrl}/deploy`, {
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
            body: JSON.stringify({ project_id: pId })
        })
        .then(res => res.json())
        .then(data => {
            deployBtn.disabled = false;
            deployBtn.textContent = 'Deploy Extension to Production ⚡';
            
            if (data.success) {
                statusBox.textContent = `PIPELINE_SUCCESS -> ${data.message}`;
                statusBox.style.color = '#34d399';
                statusBox.style.borderColor = '#065f46';
            } else {
                statusBox.textContent = `DEPLOYMENT_ABORTED -> Error Matrix Break: ${data.message}`;
                statusBox.style.color = '#f43f5e';
                statusBox.style.borderColor = '#7f1d1d';
            }
        })
        .catch(err => {
            deployBtn.disabled = false;
            deployBtn.textContent = 'Deploy Extension to Production ⚡';
            statusBox.textContent = `NETWORK_FAULT -> Connection drop exceptions tracking: ${err.message}`;
            statusBox.style.color = '#f43f5e';
        });
    });
});
</script>
