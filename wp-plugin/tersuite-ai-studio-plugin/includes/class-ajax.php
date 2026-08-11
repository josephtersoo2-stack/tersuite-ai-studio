<?php
defined('ABSPATH') || exit;

/**
 * Tersuite AI Studio AJAX Route Controller
 *
 * Routes admin AJAX requests through capability & nonce security guards to the appropriate service managers.
 */
class Tersuite_AI_AJAX {

    public function register() {
        $actions = array(
            'coordinator_context' => 'coordinator_context',
            'coordinator_message' => 'coordinator_message',
            'get_plan'            => 'get_plan',
            'approve_plan'        => 'approve_plan',
            'task_graph'          => 'task_graph',
            'session_reports'     => 'session_reports',
            'cancel_session'      => 'cancel_session',
            'dashboard'           => 'dashboard',
            'projects'            => 'projects',
            'project'             => 'project',
            'create_project'      => 'create_project',
            'files'               => 'files',
            'file'                => 'file',
            'save_file'           => 'save_file',
            'deliveries'          => 'deliveries',
            'site_inspection'     => 'site_inspection',
            'usage'               => 'usage',
            'subscription'        => 'subscription',
            'notifications'       => 'notifications',
            'test_connection'     => 'test_connection',
        );

        foreach ($actions as $action => $method) {
            add_action('wp_ajax_tersuite_' . $action, array($this, $method));
        }
    }

    /**
     * Security Guard: Check capability & nonce.
     */
    private function guard() {
        if (!Tersuite_AI_Capabilities::manage()) {
            Tersuite_AI_Error_Handler::json_error(__('Permission denied. Insufficient administrative capabilities.', 'tersuite-ai-studio'), 'permission_denied');
        }

        if (!Tersuite_AI_Nonce::verify()) {
            Tersuite_AI_Error_Handler::json_error(__('Security check failed. Please refresh the page and try again.', 'tersuite-ai-studio'), 'nonce_verification_failed');
        }
    }

    /**
     * Respond with JSON success or WP_Error formatted error.
     */
    private function respond($result) {
        if (is_wp_error($result)) {
            Tersuite_AI_Error_Handler::json_error($result, $result->get_error_code());
        }
        wp_send_json_success($result);
    }

    // --- COORDINATOR & CONTEXT ENDPOINTS ---

    public function coordinator_context() {
        $this->guard();
        $project_id = sanitize_text_field(wp_unslash($_POST['project_id'] ?? ''));
        $ui_context = isset($_POST['ui_context']) && is_array($_POST['ui_context'])
            ? array_map('sanitize_text_field', wp_unslash($_POST['ui_context']))
            : array();

        $manager = new Tersuite_AI_Coordinator_Manager();
        $this->respond($manager->get_context($project_id, $ui_context));
    }

    public function coordinator_message() {
        $this->guard();
        $project_id = sanitize_text_field(wp_unslash($_POST['project_id'] ?? ''));
        $message    = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
        $ui_context = isset($_POST['ui_context']) && is_array($_POST['ui_context'])
            ? array_map('sanitize_text_field', wp_unslash($_POST['ui_context']))
            : array();

        $manager = new Tersuite_AI_Coordinator_Manager();
        $this->respond($manager->send_message($project_id, $message, $ui_context));
    }

    // --- PRODUCTION PLANS & APPROVAL ---

    public function get_plan() {
        $this->guard();
        $project_id = sanitize_text_field(wp_unslash($_POST['project_id'] ?? ''));
        $plan_id    = sanitize_text_field(wp_unslash($_POST['plan_id'] ?? ''));

        $manager = new Tersuite_AI_Production_Plan_Manager();
        $this->respond($manager->get($project_id, $plan_id));
    }

    public function approve_plan() {
        $this->guard();
        $project_id = sanitize_text_field(wp_unslash($_POST['project_id'] ?? ''));
        $plan_id    = sanitize_text_field(wp_unslash($_POST['plan_id'] ?? ''));

        $manager = new Tersuite_AI_Production_Plan_Manager();
        $this->respond($manager->approve($project_id, $plan_id));
    }

    // --- TASK GRAPH & SESSIONS ---

    public function task_graph() {
        $this->guard();
        $project_id = sanitize_text_field(wp_unslash($_POST['project_id'] ?? ''));

        $manager = new Tersuite_AI_Task_Graph_Manager();
        $this->respond($manager->get($project_id));
    }

    public function session_reports() {
        $this->guard();
        $project_id = sanitize_text_field(wp_unslash($_POST['project_id'] ?? ''));
        $session_id = sanitize_text_field(wp_unslash($_POST['session_id'] ?? ''));

        $manager = new Tersuite_AI_Session_Report_Manager();
        $this->respond($manager->get($project_id, $session_id));
    }

    public function cancel_session() {
        $this->guard();
        $project_id = sanitize_text_field(wp_unslash($_POST['project_id'] ?? ''));
        $session_id = sanitize_text_field(wp_unslash($_POST['session_id'] ?? ''));

        $manager = new Tersuite_AI_Production_Session_Manager();
        $this->respond($manager->cancel($project_id, $session_id));
    }

    // --- PROJECTS & DASHBOARD ---

    public function dashboard() {
        $this->guard();
        $api = new Tersuite_AI_API_Client();
        $this->respond($api->get('api/v1/dashboard'));
    }

    public function projects() {
        $this->guard();
        $manager = new Tersuite_AI_Project_Manager();
        $this->respond($manager->list());
    }

    public function project() {
        $this->guard();
        $id = sanitize_text_field(wp_unslash($_POST['id'] ?? ''));
        $manager = new Tersuite_AI_Project_Manager();
        $this->respond($manager->get($id));
    }

    public function create_project() {
        $this->guard();
        $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $desc = sanitize_textarea_field(wp_unslash($_POST['description'] ?? ''));
        if ($name === '') {
            Tersuite_AI_Error_Handler::json_error(__('Project name is required.', 'tersuite-ai-studio'), 'validation_failed');
        }
        $manager = new Tersuite_AI_Project_Manager();
        $this->respond($manager->create($name, $desc, 'plugin'));
    }

    // --- FILES & WORKSPACE ---

    public function files() {
        $this->guard();
        $id = sanitize_text_field(wp_unslash($_POST['id'] ?? ''));
        $manager = new Tersuite_AI_File_Manager();
        $this->respond($manager->tree($id));
    }

    public function file() {
        $this->guard();
        $id   = sanitize_text_field(wp_unslash($_POST['id'] ?? ''));
        $path = sanitize_text_field(wp_unslash($_POST['path'] ?? ''));
        if (strpos($path, '..') !== false) {
            Tersuite_AI_Error_Handler::json_error(__('Invalid file path specified.', 'tersuite-ai-studio'), 'path_traversal');
        }
        $manager = new Tersuite_AI_File_Manager();
        $this->respond($manager->file($id, $path));
    }

    public function save_file() {
        $this->guard();
        $id      = sanitize_text_field(wp_unslash($_POST['id'] ?? ''));
        $path    = sanitize_text_field(wp_unslash($_POST['path'] ?? ''));
        $content = wp_unslash($_POST['content'] ?? '');
        if (strpos($path, '..') !== false) {
            Tersuite_AI_Error_Handler::json_error(__('Invalid file path specified.', 'tersuite-ai-studio'), 'path_traversal');
        }
        $manager = new Tersuite_AI_File_Manager();
        $this->respond($manager->save($id, $path, $content));
    }

    // --- AUXILIARY ENDPOINTS ---

    public function deliveries() {
        $this->guard();
        $id = sanitize_text_field(wp_unslash($_POST['id'] ?? ''));
        $manager = new Tersuite_AI_Delivery_Manager();
        $this->respond($manager->list($id));
    }

    public function site_inspection() {
        $this->guard();
        $inspector = new Tersuite_AI_Site_Inspector();
        wp_send_json_success($inspector->inspect());
    }

    public function usage() {
        $this->guard();
        $manager = new Tersuite_AI_Usage_Manager();
        wp_send_json_success(array('usage' => $manager->usage(), 'credits' => $manager->credits()));
    }

    public function subscription() {
        $this->guard();
        $manager = new Tersuite_AI_Subscription_Manager();
        wp_send_json_success(array('plans' => $manager->plans(), 'status' => $manager->status()));
    }

    public function notifications() {
        $this->guard();
        $manager = new Tersuite_AI_Notification_Manager();
        $this->respond($manager->list());
    }

    public function test_connection() {
        $this->guard();
        $auth = new Tersuite_AI_Auth_Manager();
        $this->respond($auth->me());
    }
}
