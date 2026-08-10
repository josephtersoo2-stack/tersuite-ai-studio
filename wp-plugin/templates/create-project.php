<?php
/**
 * Create Project template for Tersuite AI Studio.
 */
?>
<div class="wrap tersuite-create">
    <h1><?php _e('Create New Plugin Project', 'tersuite'); ?></h1>
    <form id="tersuite-create-form" method="post">
        <?php wp_nonce_field('tersuite_nonce', 'tersuite_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="project_name"><?php _e('Project Name', 'tersuite'); ?></label></th>
                <td><input type="text" id="project_name" name="project_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="plugin_description"><?php _e('Description', 'tersuite'); ?></label></th>
                <td><textarea id="plugin_description" name="plugin_description" rows="3" class="large-text"></textarea></td>
            </tr>
        </table>
        <button type="submit" class="button button-primary" id="create-project-btn">
            <?php _e('Create Project', 'tersuite'); ?>
        </button>
    </form>
    <div id="create-result"></div>
</div>
