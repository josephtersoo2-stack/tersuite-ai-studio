# WordPress ↔ Django Integration Contract

This document defines the boundary the plugin expects. These are integration placeholders until the Django backend contract is finalized; Antigravity must not silently invent incompatible routes.

## Ownership

WordPress owns presentation and browser interaction. Django owns project truth and CrewAI execution.

## Core resource families

- `/projects/{project_id}/coordinator/context`
- `/projects/{project_id}/coordinator/messages`
- `/projects/{project_id}/production-plans`
- `/projects/{project_id}/production-plans/{plan_id}/approve`
- `/projects/{project_id}/production-sessions`
- `/projects/{project_id}/production-sessions/{session_id}/cancel`
- `/projects/{project_id}/task-graph`
- `/projects/{project_id}/memory`
- `/projects/{project_id}/session-reports`

The exact prefix/version and response schema must be finalized by the Django backend before production integration.

## Event categories

The WebSocket layer should support authoritative events for:

`context.loaded`, `plan.created`, `plan.updated`, `plan.approval_required`, `production.queued`, `production.started`, `task.started`, `task.progress`, `task.completed`, `task.failed`, `worker.started`, `worker.progress`, `worker.completed`, `file.created`, `file.modified`, `file.deleted`, `validation.started`, `validation.completed`, `security.started`, `security.completed`, `review.started`, `review.completed`, `sandbox.started`, `sandbox.completed`, `delivery.ready`, `production.completed`, `production.failed`, `production.cancelled`.

These names are a target contract, not permission to invent a backend implementation. Reconcile them with Django before wiring production behavior.
