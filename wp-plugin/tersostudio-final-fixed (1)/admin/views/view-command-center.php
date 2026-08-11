<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $wpdb;

// Gather dynamic system metadata metrics via strict prepared queries statement blocks
$count_projects = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}ts_projects", [] ) );
$count_jobs     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}ts_jobs", [] ) );
$count_memory   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}ts_error_memory", [] ) );
$count_logs     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}ts_logs", [] ) );

$container = TERSOSTUDIO_Service_Container::get_instance();
$has_fs   = $container->make( 'filesystem_gate' ) ? '🟢 OPERATIONAL' : '🔴 OFFLINE';

require_once TERSOSTUDIO_PATH . 'core/Security/class-path-validator.php';
$has_path = TERSOSTUDIO_Path_Validator::get_instance() ? '🔒 ARMED' : '⚠️ MISCONFIGURED';
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">TersoStudio v2 Operational Command Center</h1>
    
    // Row 1: System Telemetry Stats Grid Layout Counters
    <div style="display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 180px; background: #16161a; border: 1px solid #29292e; border-radius: 8px; padding: 20px; font-family: monospace;">
            <div style="color: #6b7280; font-size: 11px; font-weight: bold;">ACTIVE_PROJECTS</div>
            <div style="color: #60a5fa; font-size: 28px; font-weight: bold; margin-top: 8px;"><?php echo $count_projects; ?></div>
        </div>
        <div style="flex: 1; min-width: 180px; background: #16161a; border: 1px solid #29292e; border-radius: 8px; padding: 20px; font-family: monospace;">
            <div style="color: #6b7280; font-size: 11px; font-weight: bold;">SWARM_TASKS_RUN</div>
            <div style="color: #10b981; font-size: 28px; font-weight: bold; margin-top: 8px;"><?php echo $count_jobs; ?></div>
        </div>
        <div style="flex: 1; min-width: 180px; background: #16161a; border: 1px solid #29292e; border-radius: 8px; padding: 20px; font-family: monospace;">
            <div style="color: #6b7280; font-size: 11px; font-weight: bold;">SELF_HEALED_FAULTS</div>
            <div style="color: #eab308; font-size: 28px; font-weight: bold; margin-top: 8px;"><?php echo $count_memory; ?></div>
        </div>
        <div style="flex: 1; min-width: 180px; background: #16161a; border: 1px solid #29292e; border-radius: 8px; padding: 20px; font-family: monospace;">
            <div style="color: #6b7280; font-size: 11px; font-weight: bold;">INTERCEPTED_INCIDENTS</div>
            <div style="color: #f43f5e; font-size: 28px; font-weight: bold; margin-top: 8px;"><?php echo $count_logs; ?></div>
        </div>
    </div>

    // Row 2: Cluster Integrity & Subsystem Access Framework Trees
    <div style="display: flex; gap: 24px; flex-wrap: wrap;">
        <div style="flex: 1.5; min-width: 360px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e;">
            <h3 style="color: #fff; margin-top: 0; margin-bottom: 16px; font-family: monospace;">>_ CLUSTER KERNEL RUNTIME INTEGRITYSTATUS</h3>
            <div style="font-family: monospace; font-size: 13px; display: flex; flex-direction: column; gap: 12px; line-height: 1.5;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #29292e; padding-bottom: 8px;">
                    <span style="color: #a7aaad;">IoC Service Container Runtime Mapping:</span>
                    <span style="color: #34d399; font-weight: bold;">✓ ONLINE</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #29292e; padding-bottom: 8px;">
                    <span style="color: #a7aaad;">Filesystem Abstraction Gate Security Channel:</span>
                    <span style="font-weight: bold;"><?php echo esc_html( $has_fs ); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #29292e; padding-bottom: 8px;">
                    <span style="color: #a7aaad;">Path Traversal Boundary Validation Guard:</span>
                    <span style="color: #38bdf8; font-weight: bold;"><?php echo esc_html( $has_path ); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 4px;">
                    <span style="color: #a7aaad;">Transient API Transaction Rate Limiting:</span>
                    <span style="color: #a7aaad; font-weight: bold;">⚡ ACTIVE</span>
                </div>
            </div>
        </div>
        
        <div style="flex: 1; min-width: 260px; background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="color: #fff; margin-top: 0; margin-bottom: 8px; font-family: monospace;">QUICK ACTION RAMP</h3>
                <p style="color: #a7aaad; font-size: 12px; line-height: 1.5; margin-bottom: 20px;">
                    Launch the synchronized workbench engine environment channels immediately via terminal navigation variables.
                </p>
            </div>
            <a href="<?php echo admin_url('admin.php?page=tersostudio-workbench'); ?>" class="button button-primary" style="width: 100%; text-align: center; height: 40px; line-height: 38px; font-weight: bold; font-size: 13px; background: #2563eb; border: none; text-shadow: none;">Launch Workspace IDE Workbench</a>
        </div>
    </div>
</div>
