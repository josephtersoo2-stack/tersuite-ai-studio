/**
 * Tersuite AI Studio — JavaScript API Client Adapter
 */
(function($) {
    'use strict';

    window.TSAAPI = {
        coordinatorContext: function(projectId, uiContext) {
            return TSA.ajax('coordinator_context', { project_id: projectId, ui_context: uiContext || {} });
        },
        coordinatorMessage: function(projectId, message, uiContext) {
            return TSA.ajax('coordinator_message', { project_id: projectId, message: message, ui_context: uiContext || {} });
        },
        getPlan: function(projectId, planId) {
            return TSA.ajax('get_plan', { project_id: projectId, plan_id: planId || '' });
        },
        approvePlan: function(projectId, planId) {
            return TSA.ajax('approve_plan', { project_id: projectId, plan_id: planId });
        },
        taskGraph: function(projectId) {
            return TSA.ajax('task_graph', { project_id: projectId });
        },
        sessionReports: function(projectId, sessionId) {
            return TSA.ajax('session_reports', { project_id: projectId, session_id: sessionId || '' });
        },
        cancelSession: function(projectId, sessionId) {
            return TSA.ajax('cancel_session', { project_id: projectId, session_id: sessionId });
        },
        dashboard: function() {
            return TSA.ajax('dashboard');
        },
        projects: function() {
            return TSA.ajax('projects');
        },
        project: function(id) {
            return TSA.ajax('project', { id: id });
        },
        createProject: function(name, description) {
            return TSA.ajax('create_project', { name: name, description: description });
        },
        files: function(id) {
            return TSA.ajax('files', { id: id });
        },
        file: function(id, path) {
            return TSA.ajax('file', { id: id, path: path });
        },
        saveFile: function(id, path, content) {
            return TSA.ajax('save_file', { id: id, path: path, content: content });
        },
        deliveries: function(id) {
            return TSA.ajax('deliveries', { id: id });
        },
        site: function() {
            return TSA.ajax('site_inspection');
        },
        usage: function() {
            return TSA.ajax('usage');
        },
        subscription: function() {
            return TSA.ajax('subscription');
        },
        notifications: function() {
            return TSA.ajax('notifications');
        },
        testConnection: function() {
            return TSA.ajax('test_connection');
        }
    };

})(jQuery);