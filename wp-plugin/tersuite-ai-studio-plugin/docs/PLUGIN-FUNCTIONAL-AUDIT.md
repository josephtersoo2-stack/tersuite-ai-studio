# Tersuite AI Studio — Plugin Functional Pass

## Scope
This pass changes **only the WordPress plugin**. Django/CrewAI is not modified.

The plugin remains a presentation/client layer. Django remains authoritative for project state, Coordinator intelligence, CrewAI execution, task graphs, memory, sessions, validation, sandboxing and delivery contents.

## Completed plugin-side work

- Enqueued every page-specific JavaScript module instead of loading only AI Studio modules.
- Added missing WordPress AJAX routes for versions, restore, activity, notification read, sessions, session reports, delivery creation, delivery installation, account and subscription actions.
- Removed visible static/demo project, usage, notification, subscription, activity, site and account data from the admin views.
- Added loading, empty, error and retry states.
- Added project creation modal and real create/open-Studio flow.
- Added project search, status filtering and grid/list presentation.
- Added project selector dropdown in the global header.
- Made global notification, account and production controls functional.
- Added dynamic dashboard statistics and recent activity.
- Added project-wide Files browser and preview.
- Added Versions loading and restore action.
- Added Production History backed by production sessions and cancellation.
- Added Delivery creation, download link handling and backend-authorized WordPress installation flow.
- Added dynamic Site Integration inspection.
- Added dynamic Usage & Credits rendering.
- Added dynamic Subscription plans and backend subscription submission.
- Added dynamic Notifications and Mark Read.
- Added Settings account/connection state.
- Fixed Coordinator-only chat behavior and removed specialist-agent selection from the user experience.
- Added explicit Production Plan review and approval gating.
- Added revision ID support to file saves so the backend can reject stale edits.
- Added Ctrl/Cmd+S file saving.
- Removed long-lived REST API key from browser WebSocket configuration.
- WebSocket client now supports ticket-based authentication, listener preservation, exponential reconnect backoff and clean manual disconnect.
- Added mobile Studio tabs for Explorer / Editor / Coordinator instead of simply stacking all three panels.
- Added responsive mobile states for modals, project cards, delivery cards, file browser, header and Studio.
- Added functional loading/error states across major screens.

## Validation performed

- All PHP files pass `php -l`.
- All JavaScript files pass Node `--check`.
- Every registered `wp_ajax_tersuite_*` action in `class-ajax.php` has a corresponding handler method.
- Static/demo strings previously used as project/account/usage examples were searched and removed from admin views and page scripts.

## Backend boundary

The plugin intentionally does not invent or implement CrewAI/Django behavior. It calls the documented Tersuite API resource families and consumes backend-returned state. If the Django contract uses a different finalized route or response field, the plugin's API client/manager mapping must be updated to that authoritative backend contract rather than silently creating a second protocol.
