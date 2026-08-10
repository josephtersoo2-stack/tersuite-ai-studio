<?php
/**
 * IDE Page Template for Tersuite AI Studio Plugin Generator.
 * Features: Agent chat interface, file tree/file manager, real-time progress.
 */
$project_id = sanitize_text_field($_GET['project_id'] ?? '');
$file_manager = new FileManager();
$files = $file_manager->get_file_tree($project_id);
?>
<div class="wrap tersuite-ide">
    <div class="ide-header">
        <h1><?php _e('IDE - Plugin Project', 'tersuite'); ?>: <?php echo esc_html($project_id); ?></h1>
        <div class="ide-status">
            <span class="status-indicator live" id="agent-status">Live</span>
            <span id="agent-progress-text"><?php _e('Waiting for agent updates...', 'tersuite'); ?></span>
        </div>
        <a href="#" class="button button-secondary start-pipeline" data-project-id="<?php echo esc_attr($project_id); ?>">
            <?php _e('Start Agent Pipeline', 'tersuite'); ?>
        </a>
    </div>

    <div class="ide-layout">
        <!-- Left: Agent Chat & Control -->
        <aside class="ide-sidebar">
            <h2><?php _e('Agent Chat', 'tersuite'); ?></h2>
            <div class="agent-chat-box" id="agent-chat-box">
                <div class="chat-message agent">
                    <strong><?php _e('Coordinator:', 'tersuite'); ?></strong> <?php _e('Hello! Describe your plugin requirements. I will plan with you before coding.', 'tersuite'); ?>
                </div>
            </div>

            <form id="agent-chat-form">
                <?php wp_nonce_field('tersuite_nonce', 'tersuite_nonce'); ?>
                <textarea id="agent-input" rows="3" placeholder="<?php _e('Describe plugin features, pages needed, etc.', 'tersuite'); ?>" required></textarea>
                <button type="submit" class="button button-primary"><?php _e('Send to Agents', 'tersuite'); ?></button>
            </form>

            <div class="agent-roles">
                <h3><?php _e('Active Sub-Agents', 'tersuite'); ?></h3>
                <ul>
                    <li class="agent-role" data-role="coordinator"><span class="role-icon">&#9733;</span> <?php _e('Coordinator', 'tersuite'); ?></li>
                    <li class="agent-role" data-role="ui_ux"><span class="role-icon">&#128396;</span> <?php _e('UI/UX', 'tersuite'); ?></li>
                    <li class="agent-role" data-role="backend"><span class="role-icon">&#128187;</span> <?php _e('Backend', 'tersuite'); ?></li>
                    <li class="agent-role" data-role="security"><span class="role-icon">&#128274;</span> <?php _e('Security', 'tersuite'); ?></li>
                    <li class="agent-role" data-role="coder"><span class="role-icon">&#9997;</span> <?php _e('Coder', 'tersuite'); ?></li>
                    <li class="agent-role" data-role="review"><span class="role-icon">&#128200;</span> <?php _e('Review', 'tersuite'); ?></li>
                    <li class="agent-role" data-role="sandbox"><span class="role-icon">&#128187;</span> <?php _e('Sandbox', 'tersuite'); ?></li>
                </ul>
            </div>
        </aside>

        <!-- Right: File Manager / File Tree -->
        <section class="ide-main">
            <h2><?php _e('File Tree', 'tersuite'); ?></h2>
            <div class="file-tree" id="file-tree">
                <?php if (!empty($files)) : ?>
                    <?php foreach ($files as $folder => $contents) : ?>
                        <div class="folder">
                            <span class="folder-name">&#128193; <?php echo esc_html($folder); ?></span>
                            <?php if (is_array($contents)) : ?>
                                <ul class="folder-contents">
                                    <?php foreach ($contents as $item) : ?>
                                        <?php if (is_string($item)) : ?>
                                            <li class="file-item">&#128196; <?php echo esc_html($item); ?></li>
                                        <?php else : ?>
                                            <li class="folder-item">&#128193; <?php echo esc_html($item); ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p><?php _e('No files yet. Start the agent pipeline to generate plugin code.', 'tersuite'); ?></p>
                <?php endif; ?>
            </div>

            <div class="file-preview">
                <h3><?php _e('File Preview', 'tersuite'); ?></h3>
                <pre id="file-preview-content">// Select a file to preview its content here.</pre>
            </div>
        </section>
    </div>
</div>
