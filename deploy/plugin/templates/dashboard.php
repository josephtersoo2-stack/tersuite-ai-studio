<?php
/**
 * Dashboard template for Tersuite AI Studio Plugin Generator.
 * Shows project list, quick actions, and real-time status.
 */
$project_manager = new ProjectManager();
$projects = $project_manager->get_projects();
?>
<div class="wrap tersuite-dashboard">
    <h1><?php _e('Tersuite AI Studio Plugin Generator', 'tersuite'); ?></h1>
    <p><?php _e('Create WordPress plugins with AI agents. Monitor in real-time.', 'tersuite'); ?></p>

    <div class="tersuite-stats">
        <div class="stat-box">
            <h3><?php echo esc_html(is_array($projects) ? count($projects) : 0); ?></h3>
            <p><?php _e('Active Projects', 'tersuite'); ?></p>
        </div>
        <div class="stat-box">
            <h3><?php _e('Multi-Agent', 'tersuite'); ?></h3>
            <p><?php _e('Coordinator + 7 Sub-Agents', 'tersuite'); ?></p>
        </div>
    </div>

    <a href="<?php echo admin_url('admin.php?page=tersuite-create'); ?>" class="button button-primary button-hero">
        <?php _e('+ Create New Plugin Project', 'tersuite'); ?>
    </a>

    <h2><?php _e('Your Subscription', 'tersuite'); ?></h2>
    <div id="subscription-status" class="subscription-box">
        <p><?php _e('Loading subscription details...', 'tersuite'); ?></p>
    </div>

    <h2><?php _e('Your Projects', 'tersuite'); ?></h2>
    <?php if (is_array($projects) && !empty($projects)) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Project', 'tersuite'); ?></th>
                    <th><?php _e('Status', 'tersuite'); ?></th>
                    <th><?php _e('Actions', 'tersuite'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project) : ?>
                    <tr>
                        <td><?php echo esc_html($project['name'] ?? 'Unnamed'); ?></td>
                        <td><span class="status-badge">Active</span></td>
                        <td>
                            <a href="#" class="button open-ide" data-project-id="<?php echo esc_attr($project['id'] ?? ''); ?>">
                                <?php _e('Open IDE', 'tersuite'); ?>
                            </a>
                            <?php if (($project['status'] ?? '') === 'completed') : ?>
                                <a href="#" class="button button-primary download-plugin" data-project-id="<?php echo esc_attr($project['id'] ?? ''); ?>">
                                    <?php _e('Download ZIP', 'tersuite'); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php _e('No projects yet. Create your first plugin project above.', 'tersuite'); ?></p>
    <?php endif; ?>
</div>
