<?php
/** File tree manager backed by the Tersuite API. */
final class FileManager {
    public function get_file_tree(string $project_id): array {
        $response = (new APIClient())->get("projects/{$project_id}/deliver/");
        if (!isset($response['files']) || !is_array($response['files'])) { return []; }
        $tree = [];
        foreach ($response['files'] as $path => $_content) {
            $parts = explode('/', trim((string) $path, '/'));
            $cursor =& $tree;
            foreach ($parts as $part) {
                if ($part === '') continue;
                if (!isset($cursor[$part])) $cursor[$part] = [];
                $cursor =& $cursor[$part];
            }
            unset($cursor);
        }
        return $tree;
    }

    public function read_file(string $project_id, string $file_path): string {
        $response = (new APIClient())->get("projects/{$project_id}/deliver/");
        return isset($response['files'][$file_path]) ? (string) $response['files'][$file_path] : '';
    }
}
