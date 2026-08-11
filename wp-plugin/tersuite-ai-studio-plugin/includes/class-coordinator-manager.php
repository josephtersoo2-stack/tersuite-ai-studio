<?php
defined('ABSPATH') || exit;

/**
 * Coordinator Manager
 *
 * Manages conversation with the single user-facing identity: Tersuite Coordinator.
 *
 * CRITICAL ARCHITECTURAL RULE:
 * The user communicates exclusively with the Tersuite Coordinator.
 * Specialist agents (UI/UX, Backend, Frontend, Security, Review, Sandbox)
 * are internal CrewAI execution units managed by Django.
 * This class MUST NOT expose individual specialist-agent methods.
 */
class Tersuite_AI_Coordinator_Manager {

    private $api;
    private $context_manager;

    public function __construct() {
        $this->api             = new Tersuite_AI_API_Client();
        $this->context_manager = new Tersuite_AI_Project_Context_Manager();
    }

    /**
     * Load Coordinator project context.
     *
     * @param string|int $project_id
     * @param array      $ui_context
     * @return array|WP_Error
     */
    public function get_context($project_id, $ui_context = array()) {
        return $this->context_manager->get_context($project_id, $ui_context);
    }

    /**
     * Send a message to the Tersuite Coordinator.
     *
     * @param string|int $project_id
     * @param string     $message
     * @param array      $ui_context
     * @return array|WP_Error
     */
    public function send_message($project_id, $message, $ui_context = array()) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to send messages.', 'tersuite-ai-studio'));
        }

        $clean_message = sanitize_textarea_field(wp_unslash($message));
        if (empty(trim($clean_message))) {
            return new WP_Error('empty_message', __('Message prompt cannot be empty.', 'tersuite-ai-studio'));
        }

        $payload = array(
            'message'    => $clean_message,
            'ui_context' => !empty($ui_context) ? $ui_context : $this->context_manager->ui_payload($project_id),
        );

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/coordinator/messages';
        return $this->api->post($path, $payload);
    }

    /**
     * Submit explicit user approval for a production plan.
     *
     * @param string|int $project_id
     * @param string|int $plan_id
     * @return array|WP_Error
     */
    public function approve_production($project_id, $plan_id) {
        if (empty($project_id) || empty($plan_id)) {
            return new WP_Error('invalid_params', __('Project ID and Plan ID are required for approval.', 'tersuite-ai-studio'));
        }

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/production-plans/' . rawurlencode((string) $plan_id) . '/approve';
        return $this->api->post($path);
    }

    /**
     * Retrieve session report / summary.
     *
     * @param string|int $project_id
     * @param string|null $session_id
     * @return array|WP_Error
     */
    public function get_session_summary($project_id, $session_id = null) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to fetch session summaries.', 'tersuite-ai-studio'));
        }

        $path = 'api/v1/projects/' . rawurlencode((string) $project_id) . '/session-reports';
        if ($session_id !== null && $session_id !== '') {
            $path .= '/' . rawurlencode((string) $session_id);
        }

        return $this->api->get($path);
    }
}
