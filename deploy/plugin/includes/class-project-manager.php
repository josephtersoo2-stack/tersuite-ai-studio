<?php
/** Project manager for plugin generation projects. */
final class ProjectManager {
    public function get_projects(): array {
        $response = (new APIClient())->get('projects/');
        return isset($response['results']) && is_array($response['results']) ? $response['results'] : (is_array($response) ? $response : []);
    }

    public function create_project(string $name, string $description = ''): array {
        return (new APIClient())->post('projects/', ['name' => $name, 'description' => $description]);
    }

    public function delete_project(string $project_id): array {
        return (new APIClient())->delete("projects/{$project_id}/");
    }
}
