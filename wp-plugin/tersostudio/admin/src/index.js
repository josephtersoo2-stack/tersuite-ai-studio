import { WorkspaceMainLayoutShell } from './components/Workspace/WorkspaceShell.js';

(function() {
    function InitialiseWorkspaceIDE() {
        const mountNode = document.getElementById('tersostudio-workbench-root');
        if (!mountNode) return;

        if (wp.element.createRoot) {
            wp.element.createRoot(mountNode).render(wp.element.createElement(WorkspaceMainLayoutShell));
        } else {
            wp.element.render(wp.element.createElement(WorkspaceMainLayoutShell), mountNode);
        }
    }

    document.addEventListener('DOMContentLoaded', InitialiseWorkspaceIDE);
})();
