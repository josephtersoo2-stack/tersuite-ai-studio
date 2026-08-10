<?php
namespace {{PLUGIN_NAMESPACE}}\Admin;

/**
 * Admin settings page with security best practices.
 */
final class SettingsPage {
    public function render(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Access denied.', '{{TEXT_DOMAIN}}'));
        }
        $options = get_option('{{OPTION_NAME}}', []);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('{{OPTION_GROUP}}'); ?>
                <?php do_settings_sections('{{SLUG}}'); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
