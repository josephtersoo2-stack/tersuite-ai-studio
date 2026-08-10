<?php
/**
 * IDE page with chat interface for user-agent collaboration.
 */
final class IDEPage {
    public function render_chat_interface(string $project_id): void {
        include TERSUITE_PATH . 'templates/ide-chat.php';
    }

    public function render_file_tree(string $project_id): void {
        $file_manager = new FileManager();
        $tree = $file_manager->get_file_tree($project_id);
        include TERSUITE_PATH . 'templates/file-tree.php';
    }
}
