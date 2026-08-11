<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $wpdb;

$query = $wpdb->prepare( "SELECT id, log_level, message, context, logged_at FROM {$wpdb->prefix}ts_logs ORDER BY id DESC LIMIT 50", [] );
$log_rows = $wpdb->get_results( $query, ARRAY_A ) ?: [];
?>
<div class="wrap">
    <h1 style="margin-bottom: 24px; color: #1d2327;">Ecosystem Kernel Central Diagnostic System Logs</h1>
    
    <div style="background: #1e1e1e; color: #fff; padding: 24px; border-radius: 8px; border: 1px solid #29292e;">
        <h2 style="color: #fff; margin-top: 0; margin-bottom: 16px;">📋 Intercepted Exceptions & Telemetry Records</h2>
        <p style="color: #a7aaad; font-size: 13px; margin-bottom: 20px;">
            This interface monitors real-time diagnostic entries caught by your kernel boundaries. Stack traces from fatal uncaught exceptions are fully tracked below for isolated debugging analyses.
        </p>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-family: monospace; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid #3c3c43; color: #a7aaad;">
                        <th style="padding: 12px 8px;">Log ID</th>
                        <th style="padding: 12px 8px;">Severity Level</th>
                        <th style="padding: 12px 8px;">Exception Incident Message</th>
                        <th style="padding: 12px 8px;">Trace Context JSON Parameters</th>
                        <th style="padding: 12px 8px;">Logged Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $log_rows ) ) : ?>
                        <tr><td colspan="5" style="padding: 20px 8px; color: #6b7280; font-style: italic;">No incident metrics cached inside the database tracking logs.</td></tr>
                    <?php else : ?>
                        <?php foreach ( $log_rows as $row ) : ?>
                            <?php 
                            $level = strtoupper( $row['log_level'] );
                            $badge_color = '#a7aaad';
                            if ( 'FATAL' === $level || 'CRITICAL' === $level ) $badge_color = '#f43f5e';
                            if ( 'ERROR' === $level ) $badge_color = '#f97316';
                            ?>
                            <tr style="border-bottom: 1px solid #29292e;">
                                <td style="padding: 14px 8px; color: #60a5fa;">#LOG_<?php echo intval( $row['id'] ); ?></td>
                                <td style="padding: 14px 8px;"><span style="color: <?php echo esc_attr( $badge_color ); ?>; font-weight: bold;">[<?php echo esc_html( $level ); ?>]</span></td>
                                <td style="padding: 14px 8px; color: #fff; font-weight: bold; white-space: pre-wrap;"><?php echo esc_html( $row['message'] ); ?></td>
                                <td style="padding: 14px 8px; color: #a7aaad; font-size: 11px; max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo esc_attr( $row['context'] ); ?>"><?php echo esc_html( $row['context'] ); ?></td>
                                <td style="padding: 14px 8px; color: #6b7280;"><?php echo esc_html( $row['logged_at'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
