<?php
/**
 * Settings page for API keys and backend connection.
 */
?>
<div class="wrap tersuite-settings">
    <h1><?php _e('Tersuite AI Studio Settings', 'tersuite'); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('tersuite_options');
        do_settings_sections('tersuite');
        ?>
        <table class="form-table">
            <tr>
                <th><label for="tersuite_api_base"><?php _e('Backend API URL', 'tersuite'); ?></label></th>
                <td><input type="url" name="tersuite_api_base" id="tersuite_api_base" value="<?php echo esc_attr(get_option('tersuite_api_base', 'http://localhost:8000/api/')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="tersuite_api_key"><?php _e('API Key', 'tersuite'); ?></label></th>
                <td><input type="password" name="tersuite_api_key" id="tersuite_api_key" value="<?php echo esc_attr(get_option('tersuite_api_key', '')); ?>" class="regular-text"></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
