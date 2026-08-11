<?php
defined('ABSPATH') || exit;

/**
 * File Manager
 *
 * Implements workspace file operations: tree listing, file reading, and saving edits to Django.
 */
class Tersuite_AI_File_Manager {

    protected $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    private function project_base($id) {
        return 'api/v1/projects/' . rawurlencode((string) $id) . '/files/';
    }

    /**
     * Get workspace file manifest / tree.
     */
    public function tree($project_id) {
        if (empty($project_id)) {
            return new WP_Error('missing_project_id', __('Project ID is required to list files.', 'tersuite-ai-studio'));
        }
        return $this->api->get($this->project_base($project_id));
    }

    /**
     * Get contents of a specific file.
     */
    public function file($project_id, $path) {
        if (empty($project_id) || empty($path)) {
            return new WP_Error('invalid_params', __('Project ID and file path are required.', 'tersuite-ai-studio'));
        }
        return $this->api->get($this->project_base($project_id) . rawurlencode($path));
    }

    /**
     * Save updated file contents back to Django workspace.
     */
    public function save($project_id, $path, $content, $revision = '') {
        if (empty($project_id) || empty($path)) {
            return new WP_Error('invalid_params', __('Project ID and file path are required to save file.', 'tersuite-ai-studio'));
        }

        $payload = array(
            'file_path' => sanitize_text_field($path),
            'content' => $content,
            'revision_id' => sanitize_text_field($revision),
        );

        return $this->api->post($this->project_base($project_id), $payload);
    }
}
