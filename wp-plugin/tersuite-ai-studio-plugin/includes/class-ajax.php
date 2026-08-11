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
            'delete_project'      => 'delete_project',
            'files'               => 'files',
            'file'                => 'file',
            'save_file'           => 'save_file',
            'deliveries'          => 'deliveries',
            'site_inspection'     => 'site_inspection',
            'usage'               => 'usage',
            'subscription'        => 'subscription',
            'notifications'       => 'notifications',
            'test_connection' => 'test_connection', 'versions'=>'versions', 'restore_version'=>'restore_version', 'activity'=>'activity', 'mark_notification_read'=>'mark_notification_read', 'session_reports_list'=>'session_reports_list', 'sessions'=>'sessions', 'deliver'=>'deliver', 'install_delivery'=>'install_delivery', 'account'=>'account', 'subscribe'=>'subscribe', 'upload_chat_attachment'=>'upload_chat_attachment',
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
        $attachments = array();
        if (isset($_POST['attachments'])) {
            $raw_attachments = wp_unslash($_POST['attachments']);
            if (is_string($raw_attachments)) { $raw_attachments = json_decode($raw_attachments, true); }
            if (is_array($raw_attachments)) { $attachments = $raw_attachments; }
        }
        $this->respond($manager->send_message($project_id, $message, $ui_context, $attachments));
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
            Tersuite_AI_Error_Handler::json_error(
                __('Project name is required.', 'tersuite-ai-studio'),
                'validation_failed'
            );
        }

        $manager = new Tersuite_AI_Project_Manager();
        $result  = $manager->create($name, $desc, 'plugin');

        if (is_wp_error($result)) {
            $this->respond($result);
        }

        // Normalize common Django/DRF response envelopes.
        $normalized = is_array($result) ? $result : array();
        $candidates = array($normalized);

        foreach (array('data', 'project', 'result') as $key) {
            if (isset($normalized[$key]) && is_array($normalized[$key])) {
                $candidates[] = $normalized[$key];
            }
        }

        $id = '';
        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            $id = $candidate['id']
                ?? $candidate['project_id']
                ?? ($candidate['project']['id'] ?? '');
            if ($id) {
                $normalized = array_merge($normalized, $candidate);
                break;
            }
        }

        // If the POST succeeded but did not contain an ID, reconcile against
        // the authoritative project collection. This is deliberately done
        // server-side so the browser never has to guess whether creation worked.
        if (!$id) {
            $list = $manager->list();
            if (!is_wp_error($list) && is_array($list)) {
                $collections = array($list);
                foreach (array('data', 'results', 'projects') as $key) {
                    if (isset($list[$key]) && is_array($list[$key])) {
                        $collections[] = $list[$key];
                    }
                }

                $items = array();
                foreach ($collections as $collection) {
                    if (array_is_list($collection)) {
                        $items = array_merge($items, $collection);
                    }
                }

                // Prefer an exact name match. If duplicate names exist, the
                // most recently returned matching record is used.
                foreach ($items as $item) {
                    if (!is_array($item) || !isset($item['name'])) {
                        continue;
                    }
                    if (strcasecmp((string) $item['name'], $name) === 0) {
                        $candidate_id = $item['id'] ?? $item['project_id'] ?? '';
                        if ($candidate_id) {
                            $id = $candidate_id;
                            $normalized = array_merge($item, array(
                                'created' => true,
                                'reconciled' => true,
                            ));
                        }
                    }
                }
            }
        }

        if (!$id) {
            // Creation may still have succeeded. Do NOT turn this into a
            // generic failure. Tell the frontend exactly what happened so it
            // can perform one user-visible recovery refresh.
            $this->respond(array(
                'created'          => true,
                'recovery_required'=> true,
                'project_name'     => $name,
                'message'          => __('Project was created, but the backend did not expose its ID yet. Refreshing your project list may resolve it.', 'tersuite-ai-studio'),
            ));
        }

        $normalized['created']    = true;
        $normalized['id']         = $id;
        $normalized['project_id'] = $id;

        $this->respond($normalized);
    }

    public function upload_chat_attachment() {
        $this->guard();
        if (empty($_FILES['file']) || !isset($_FILES['file']['tmp_name'])) {
            $this->respond(new WP_Error('missing_file', __('Please select a file to attach.', 'tersuite-ai-studio')));
        }
        $file = $_FILES['file'];
        if (!empty($file['error'])) {
            $this->respond(new WP_Error('upload_error', __('The attachment upload failed.', 'tersuite-ai-studio')));
        }
        if ((int)$file['size'] > 10 * 1024 * 1024) {
            $this->respond(new WP_Error('file_too_large', __('Attachments are limited to 10 MB.', 'tersuite-ai-studio')));
        }
        $allowed = array(
            'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp',
            'pdf' => 'application/pdf', 'txt' => 'text/plain', 'md' => 'text/markdown', 'csv' => 'text/csv',
            'json' => 'application/json', 'zip' => 'application/zip',
        );
        $type = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $allowed);
        if (empty($type['type']) || !in_array($type['type'], array_values($allowed), true)) {
            $this->respond(new WP_Error('file_type_not_allowed', __('This attachment type is not supported.', 'tersuite-ai-studio')));
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $upload_dir = wp_upload_dir();
        $target = trailingslashit($upload_dir['basedir']) . 'tersuite-chat';
        if (!wp_mkdir_p($target)) {
            $this->respond(new WP_Error('upload_directory_failed', __('Unable to prepare the attachment directory.', 'tersuite-ai-studio')));
        }
        $chat_upload_filter = function($dirs) use ($target, $upload_dir) {
            $dirs['path'] = $target;
            $dirs['url'] = trailingslashit($upload_dir['baseurl']) . 'tersuite-chat';
            $dirs['subdir'] = '/tersuite-chat';
            return $dirs;
        };
        add_filter('upload_dir', $chat_upload_filter);
        $uploaded = wp_handle_upload($file, array('test_form' => false, 'mimes' => $allowed));
        remove_filter('upload_dir', $chat_upload_filter);
        if (isset($uploaded['error'])) {
            $this->respond(new WP_Error('upload_failed', $uploaded['error']));
        }
        $this->respond(array(
            'name' => sanitize_file_name($file['name']),
            'url' => esc_url_raw($uploaded['url']),
            'mime' => sanitize_text_field($uploaded['type']),
            'size' => (int)$file['size'],
        ));
    }

    public function delete_project() {
        $this->guard();
        $id = sanitize_text_field(wp_unslash($_POST['id'] ?? ''));
        $this->respond((new Tersuite_AI_Project_Manager())->delete($id));
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
        $revision = sanitize_text_field(wp_unslash($_POST['revision_id'] ?? ''));
        if (strpos($path, '..') !== false) {
            Tersuite_AI_Error_Handler::json_error(__('Invalid file path specified.', 'tersuite-ai-studio'), 'path_traversal');
        }
        $manager = new Tersuite_AI_File_Manager();
        $this->respond($manager->save($id, $path, $content, $revision));
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

    public function versions() { $this->guard(); $this->respond((new Tersuite_AI_Version_Manager())->list(sanitize_text_field(wp_unslash($_POST['id'] ?? '')))); }
    public function restore_version() { $this->guard(); $this->respond((new Tersuite_AI_Version_Manager())->restore(sanitize_text_field(wp_unslash($_POST['project_id'] ?? '')), sanitize_text_field(wp_unslash($_POST['version_id'] ?? '')))); }
    public function activity() { $this->guard(); $this->respond((new Tersuite_AI_Activity_Manager())->list(sanitize_text_field(wp_unslash($_POST['project_id'] ?? '')) ?: null)); }
    public function mark_notification_read() { $this->guard(); $this->respond((new Tersuite_AI_Notification_Manager())->mark_read(sanitize_text_field(wp_unslash($_POST['id'] ?? '')))); }
    public function session_reports_list() { $this->guard(); $this->respond((new Tersuite_AI_Session_Report_Manager())->list(sanitize_text_field(wp_unslash($_POST['project_id'] ?? '')))); }
    public function sessions() { $this->guard(); $this->respond((new Tersuite_AI_Production_Session_Manager())->list(sanitize_text_field(wp_unslash($_POST['project_id'] ?? '')))); }
    public function deliver() { $this->guard(); $this->respond((new Tersuite_AI_Delivery_Manager())->deliver(sanitize_text_field(wp_unslash($_POST['project_id'] ?? '')))); }
    public function install_delivery() { $this->guard(); $this->respond((new Tersuite_AI_Delivery_Manager())->install(sanitize_text_field(wp_unslash($_POST['project_id'] ?? '')), sanitize_text_field(wp_unslash($_POST['delivery_id'] ?? '')))); }
    public function account() { $this->guard(); $this->respond((new Tersuite_AI_Auth_Manager())->me()); }
    public function subscribe() { $this->guard(); $this->respond((new Tersuite_AI_Subscription_Manager())->subscribe(sanitize_text_field(wp_unslash($_POST['plan'] ?? '')), sanitize_key(wp_unslash($_POST['gateway'] ?? '')))); }
}
