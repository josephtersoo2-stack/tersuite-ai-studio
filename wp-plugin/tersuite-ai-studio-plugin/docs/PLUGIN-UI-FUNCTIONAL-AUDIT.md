# Tersuite AI Studio — Plugin UI/UX Functional Pass

## Scope
This pass modifies the WordPress plugin only. No Django/CrewAI backend code was changed.

## Major fixes
- Restored the missing `.tsa-main-full` layout container so sidebar + main content render as a single responsive application shell.
- Added stable desktop/tablet/mobile shell sizing and WordPress admin-bar-aware positioning.
- Added keyboard focus states, disabled states, mobile sidebar close behavior, and Escape handling.
- Reworked project card overflow menu so it is an actual UI menu rather than a browser prompt.
- Project deletion now has a visible action, confirmation, loading state, success/error state, and list refresh.
- Added retry states to versions, deliveries, activity, generations, usage, files, notifications and subscription.
- Notifications now handle server-provided unread counts, empty states, retry, missing IDs, and mark-read loading/error states.
- Files page now supports flat and nested workspace responses and preserves the selected project ID for the session.
- Versions now provide an actual change-details modal using version metadata returned by the backend rather than a dead toast-only action.
- Subscription gateway selection now uses an in-app modal instead of a browser prompt.
- Production-plan modal open/close state is synchronized with the plugin modal CSS state.
- Mobile Studio continues to use Explorer/Editor/Coordinator tabs instead of stacking all three large panels into one scroll-heavy page.

## Validation performed
- PHP lint across every PHP file: passed.
- JavaScript syntax validation across every JS file: passed.
- AJAX action-to-handler mapping: verified.
- Static UI selector audit: verified expected page selectors; shared header selectors are supplied by the header partial.
- Static placeholder-data search: no legacy dashboard/project demo values remain in the audited views/scripts.

## Backend boundary
The plugin continues to call the existing documented Django API contract. Any response-shape or route mismatch that only the live Django server can reveal must be tested against that backend separately.
