# Tersuite AI Studio — Antigravity Implementation Plan v3

## 0. Mission

Implement the existing WordPress plugin foundation without redesigning its UI or inventing a competing architecture. The plugin is the user-facing client for the Tersuite Django backend.

**Authoritative architecture:**

- WordPress plugin: UI, user interaction, WordPress environment inspection, REST/WebSocket client, local UI state.
- Django: authoritative project state, project memory, production plans, task graph, sessions, files/versions metadata, billing/usage, security policy, delivery state.
- CrewAI: internal multi-agent execution engine inside Django/backend workers. It is never exposed as a user-selectable agent system in WordPress.
- CrewAI Flows: production orchestration/stateful control layer for Coordinator conversations and production workflows.
- CrewAI Crews: specialist autonomous workgroups used by production stages.
- Celery/Redis: asynchronous execution and queueing.
- PostgreSQL: durable backend state.
- WebSocket: real-time backend-to-plugin telemetry.

## 1. Non-negotiable product behavior

1. The user talks to exactly one conversational identity: **Tersuite Coordinator**.
2. Never expose a specialist-agent selector in the UI.
3. Specialist agents are internal workers. Their status may be displayed read-only.
4. User approval is required before production execution starts.
5. Independent production tasks may execute concurrently, but only the backend task graph decides concurrency and dependencies.
6. Agents must not blindly edit the same file concurrently. Backend must establish task/file ownership or locking.
7. WordPress must never run CrewAI locally.
8. WordPress must never contain provider secrets or direct model API keys.
9. Django is authoritative for project memory, versions, sessions, task graph, reports and delivery state.
10. Generated file paths must be preserved exactly; never flatten project trees.
11. Every completed production session must produce and persist a comprehensive Session Completion Report.
12. The Coordinator must understand both the current project and the Tersuite UI/navigation.
13. When Studio opens, load the project context before allowing normal Coordinator conversation.
14. Do not invent API routes. If the Django contract differs, update the documented contract and implement both sides deliberately.

## 2. CrewAI implementation model

Do NOT implement a single giant Crew as the Tersuite orchestration layer.

Use the following conceptual model:

```text
Coordinator Flow
  -> project context
  -> conversation / intent
  -> planning
  -> human approval
  -> production Flow
       -> dependency-aware task graph
       -> specialist Crews/Agents
       -> review/security
       -> sandbox
       -> delivery
       -> session report
       -> project memory
```

The plugin must only call the Coordinator/production API and observe events. It does not call CrewAI directly.

## 3. Files to implement

### `includes/class-api-client.php`
Implement authenticated HTTP transport only. Handle JSON, timeouts, request IDs, status errors, safe error messages and TLS verification. Do not put business logic here.

### `includes/class-coordinator-manager.php`
Implement the single user-facing Coordinator contract:
- load Coordinator/project context;
- send conversation messages;
- request/view plans;
- approve production;
- retrieve session summaries.
Never add specialist-agent chat methods.

### `includes/class-project-context-manager.php`
Build the UI context payload:
- project ID;
- current screen/route;
- selected file;
- selected version;
- active production session;
- current UI state.
Django supplies authoritative project data.

### `includes/class-project-memory-manager.php`
Read/append project memory through Django. Do not create a second local memory database.

### `includes/class-production-plan-manager.php`
Load plans and submit explicit user approval. Never auto-approve.

### `includes/class-production-session-manager.php`
Read session state and expose safe cancellation. Do not start agents directly.

### `includes/class-task-graph-manager.php`
Read the backend dependency graph and expose read-only task status to the UI.

### `includes/class-session-report-manager.php`
Retrieve persisted session reports. Reports must expose:
- session status;
- work completed;
- files created/modified/deleted;
- tasks;
- worker activity;
- validation;
- security checks;
- review;
- sandbox;
- known issues;
- remaining work;
- recommended next step;
- exact user action required.

### `includes/class-websocket-client.php`
Implement the browser-side WebSocket configuration/handshake contract. The backend owns event truth. Do not infer completion from arbitrary client timers.

### `admin/views/ai-studio.php`
Keep the three-panel Studio. Ensure the right panel is Coordinator-only. Production Team is read-only telemetry. Add clear plan/approval/session-report states.

### `assets/js/ai-studio.js`
Implement Studio state orchestration:
- load project context on entry;
- bind Coordinator chat;
- show loading/error/empty states;
- display plan;
- request approval;
- subscribe to live session events;
- refresh session report;
- preserve current file/version/session context.

### `assets/js/assistant.js`
Only handle Coordinator conversation. Remove all specialist-agent selection behavior. Include current project/UI context with messages.

### `assets/js/agent-activity.js`
Render internal worker telemetry only. No controls to invoke agents, reassign agents, or select a CrewAI worker.

### `assets/js/websocket.js`
Map backend events to:
- coordinator state;
- task graph state;
- worker activity;
- file changes;
- validation;
- security;
- review;
- sandbox;
- delivery;
- session completion.

### `assets/js/file-tree.js` / `editor.js`
Treat backend workspace/version metadata as authoritative. Never fabricate generated files.

## 4. Coordinator context contract

When Studio opens, the plugin should request context before presenting the normal welcome state.

The backend context should contain, where available:

- original project brief;
- requirements;
- approved production plan;
- current plan version;
- completed tasks;
- pending tasks;
- blocked tasks;
- current version;
- workspace/file manifest;
- recent production sessions;
- latest session report;
- known issues;
- validation/security/sandbox results;
- delivery state;
- safe WordPress environment information;
- current screen/file/version/session context.

The Coordinator uses this context to avoid asking the user to repeat what the project is about.

## 5. Production lifecycle

The UI must represent these states:

`DRAFT -> PLAN_READY -> USER_APPROVAL_REQUIRED -> APPROVED -> QUEUED -> RUNNING -> REVIEW -> SANDBOX -> COMPLETED`

Failure/cancellation/block states:

`RUNNING -> FAILED | CANCELLED | BLOCKED`

Do not show "complete" merely because a request returned HTTP 200. Completion comes from authoritative backend state/events.

## 6. Parallel execution contract

The plugin does not schedule agents. Django/CrewAI does.

The backend should create a dependency graph such as:

```text
Requirements
   -> Architecture
      -> UI/UX ─────┐
      -> Backend ───┼-> Integration -> Security/Review -> Sandbox -> Delivery
      -> Frontend ──┘
```

Independent branches may execute simultaneously. Dependent branches wait for prerequisites.

## 7. Navigation-aware Coordinator

The Coordinator must understand Tersuite navigation. The plugin should provide current route/screen context so answers such as these work:

- “Where do I download the generated plugin?” -> Deliveries.
- “Where do I see what changed?” -> Versions / Activity.
- “Where can I see agent progress?” -> AI Studio Production Team / Activity.
- “Where is the latest report?” -> AI Studio Session Summary / Activity.
- “Where do I connect the backend?” -> Settings.
- “Can I install the validated package?” -> Deliveries after sandbox approval.

Do not hard-code natural-language answers in JavaScript. The Coordinator should answer using its product knowledge; the UI supplies the current location.

## 8. Session completion report

Every production session must end with a persisted report. The plugin should display it as structured UI and allow expansion.

Required report sections:
1. Session status and timing.
2. User request/approved objective.
3. Plan executed.
4. Tasks completed/failed/blocked.
5. Internal workers/crews involved.
6. Files created.
7. Files modified.
8. Files deleted.
9. Validation results.
10. Security results.
11. Review results.
12. Sandbox results.
13. Delivery/package result.
14. Known issues.
15. Remaining tasks.
16. Recommended next step.
17. Exact user action required.

## 9. Security

- WordPress capability checks.
- Nonces for state-changing admin AJAX.
- Sanitize and validate IDs/paths.
- Escape rendered backend data.
- Never trust file paths from the browser.
- Never store provider keys in frontend JS.
- Never expose internal CrewAI prompts, hidden reasoning, or secrets.
- Do not allow arbitrary backend URLs from untrusted requests.
- Use TLS verification.
- Backend remains responsible for sandboxing generated code.

## 10. Error handling

Errors must be explicit and actionable:

- backend unavailable;
- authentication failure;
- context load failure;
- plan unavailable;
- approval rejected;
- session failed;
- WebSocket disconnected;
- file unavailable;
- delivery unavailable.

Never silently fall back to fake/demo data in production.

## 11. Mobile behavior

Keep the existing mobile design:
- sidebar drawer;
- AI Studio Explorer/Editor/Assistant tabs;
- touch-friendly controls;
- horizontally scrollable worker activity;
- responsive session reports;
- no specialist selector.

## 12. Implementation order

1. Verify backend API contract.
2. Implement API transport/auth/error handling.
3. Implement project context.
4. Implement Coordinator conversation.
5. Implement plan retrieval and approval.
6. Implement production session/task graph state.
7. Implement WebSocket event mapping.
8. Implement read-only internal worker telemetry.
9. Implement workspace/version synchronization.
10. Implement persisted session report UI.
11. Implement delivery state and installation actions.
12. Implement navigation-aware Coordinator context.
13. Complete security hardening.
14. Test desktop/mobile and failure states.

## 13. Definition of Done

The implementation is complete only when:

- user can open a project and Coordinator knows the project context;
- user can plan in one Coordinator chat;
- user can review a production plan;
- production cannot start without explicit approval;
- backend can run independent tasks concurrently;
- plugin shows read-only worker telemetry;
- plugin never exposes a specialist-agent selector;
- file/version state remains authoritative to Django;
- live events update the UI accurately;
- completed sessions produce comprehensive reports;
- reports persist across sessions;
- Coordinator can guide users through Tersuite navigation;
- sandbox/delivery state is accurately represented;
- mobile UX remains functional;
- no fake data is used for backend-connected states;
- PHP/JS linting and integration tests pass.

## 14. Do not invent

If a required backend endpoint, event name, CrewAI Flow, Crew, tool, model, or schema is not defined by the backend contract, stop and update the contract rather than inventing an implementation. The WordPress plugin must remain a thin, stable client of the Django platform.
