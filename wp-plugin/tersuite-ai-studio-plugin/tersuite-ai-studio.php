<?php
/**
 * Plugin Name: Tersuite AI Studio
 * Plugin URI: https://tersuite.com/
 * Description: AI-powered WordPress plugin development studio connected to the Tersuite Django backend.
 * Version: 0.3.0
 * Author: Tersuite
 * License: GPL-2.0-or-later
 * Text Domain: tersuite-ai-studio
 */

defined('ABSPATH') || exit;

define('TERSUITE_AI_VERSION', '0.3.0');
define('TERSUITE_AI_FILE', __FILE__);
define('TERSUITE_AI_DIR', plugin_dir_path(__FILE__));
define('TERSUITE_AI_URL', plugin_dir_url(__FILE__));

require_once TERSUITE_AI_DIR . 'includes/class-settings.php';
require_once TERSUITE_AI_DIR . 'includes/class-capabilities.php';
require_once TERSUITE_AI_DIR . 'includes/class-nonce.php';
require_once TERSUITE_AI_DIR . 'includes/class-error-handler.php';
require_once TERSUITE_AI_DIR . 'includes/class-api-client.php';
require_once TERSUITE_AI_DIR . 'includes/class-auth-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-project-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-generation-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-coordinator-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-project-memory-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-production-plan-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-production-session-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-task-graph-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-session-report-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-project-context-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-file-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-version-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-delivery-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-zip-packager.php';
require_once TERSUITE_AI_DIR . 'includes/class-plugin-installer.php';
require_once TERSUITE_AI_DIR . 'includes/class-site-inspector.php';
require_once TERSUITE_AI_DIR . 'includes/class-usage-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-subscription-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-activity-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-notification-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-websocket-client.php';
require_once TERSUITE_AI_DIR . 'includes/class-dashboard-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-asset-manager.php';
require_once TERSUITE_AI_DIR . 'includes/class-ajax.php';
require_once TERSUITE_AI_DIR . 'includes/class-admin-menu.php';
require_once TERSUITE_AI_DIR . 'includes/class-plugin.php';

register_activation_hook(__FILE__, array('Tersuite_AI_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('Tersuite_AI_Plugin', 'deactivate'));
Tersuite_AI_Plugin::instance();
