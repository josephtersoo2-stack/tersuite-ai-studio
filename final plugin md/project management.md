<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# Yes please expand the Project Management module next

Absolutely — here is the expanded **Project Management** module in the same detailed format, aligned with the auth-first structure and ready to save as a Markdown spec. It includes lifecycle control, environment capture, module attachment, audit logging, and menu impact rules. [^1][^2][^3][^4][^5]

***

# Project Management

## Overview

The Project Management module is the core container for all work in the platform. It tracks plugin builds, plugin fixes, plugin enhancements, theme work, migrations, audits, optimizations, and future module-driven tasks. It stores project state, environment context, attached modules, entitlement status, access rights, and audit history.

This module is the backbone of the system because every AI conversation, feature tree, code generation task, sandbox test, and release action belongs to a project. [^1][^2][^4][^5]

## Purpose

- Create and manage projects.
- Track the lifecycle of each project from planning to release.
- Store WordPress and PHP environment context.
- Support add-ons and subscription-based modules.
- Enforce ownership and access control.
- Keep a full audit trail of project activity.
- Define the menu item or submenu location for project screens.
- Update menu placement when new project-related screens are added. [^1][^2][^3][^4]


## Scope

### Included

- Create project.
- View project.
- Update project.
- Archive project.
- Duplicate project.
- Track project status.
- Store metadata.
- Attach and detach modules.
- Enforce entitlements.
- Log activity and changes.
- Manage menu placement for project screens.


### Excluded

- Feature branch expansion.
- Chat system details.
- Agent orchestration.
- Sandbox execution.
- Packaging and delivery.
- UI/UX component styling outside the project area.


## Core Entities

- Project.
- ProjectModule.
- ProjectEntitlement.
- ProjectActivityLog.
- ProjectNote.
- ProjectSnapshot.


## Menu Structure

Project Management should have a clear location in the admin sidebar or workspace navigation. When project screens change, the menu structure should be updated if needed.

### Suggested menu items

- **Project Management**
    - Projects
    - Create Project
    - Project Detail
    - Duplicate Project
    - Archive Project
    - Project History
    - Project Settings


### Menu update rule

If a new project-related screen is added later, the AI should update the menu item, submenu, or child route if needed. This includes renaming items, adding child screens, changing order, or moving items under a new parent.

## Supported Project Types

- New plugin.
- Plugin fix.
- Plugin enhancer.
- Theme build.
- Theme fix.
- Migration.
- Audit.
- Optimization.
- Custom module task.


## Lifecycle States

- planning
- designing
- coding
- reviewing
- testing
- packaging
- ready
- failed
- archived


## Main Workflows

### Create project

A user creates a new project with a name, description, project type, category, workflow mode, and environment details. The system validates the request, checks entitlements, stores the project, and sets it to planning.

### View project

The system loads the project summary, current status, attached modules, latest snapshot, and audit history. Access is allowed only if the user owns the project or belongs to the workspace.

### Update project

Users can update editable fields such as name, description, category, workflow mode, and notes. Every change must be logged.

### Archive project

Finished or inactive projects can be archived so they no longer appear in active workflows.

### Duplicate project

A project can be cloned to create a new starting point for similar work. Some fields can be copied while others may be reset depending on project type and entitlement.

### Manage modules

Modules can be attached or removed based on project type and subscription rights. Each module attachment should be validated against entitlement rules before it becomes active.

### Capture snapshot

When important project changes happen, the system should store a project snapshot so the AI can reconstruct the current state later.

## Audit Trail Rules

Every important project change should generate an audit entry. That includes:

- project creation,
- updates,
- archive and restore,
- module attach and detach,
- entitlement changes,
- ownership changes,
- status changes,
- snapshot creation. [^1][^6][^3][^5]

Each audit entry should store:

- who made the change,
- what changed,
- when it changed,
- the previous state,
- the new state,
- the related project ID,
- optional metadata.


## Snapshot Rules

Project snapshots should preserve the current state of the project in a structured form. A snapshot can include:

- project metadata,
- current status,
- selected modules,
- environment info,
- approved feature specs,
- latest summary notes,
- relevant build state.

Snapshots help the AI continue from the latest known state without re-reading every chat or log entry.

## Access Control Rules

Access control should be enforced server-side for every project action. A user should only be able to:

- view projects they own or are allowed to access,
- edit projects they own or are permitted to manage,
- archive, duplicate, or delete projects only when allowed by policy,
- attach modules only when the entitlement allows it. [^1][^2][^4]

This should be handled with policies and permission checks, not only frontend hiding.

## API Endpoints

- `POST /api/projects`
- `GET /api/projects`
- `GET /api/projects/{id}`
- `PATCH /api/projects/{id}`
- `POST /api/projects/{id}/archive`
- `POST /api/projects/{id}/duplicate`
- `PATCH /api/projects/{id}/status`
- `PATCH /api/projects/{id}/metadata`
- `POST /api/projects/{id}/modules`
- `DELETE /api/projects/{id}/modules/{moduleKey}`
- `GET /api/projects/{id}/history`
- `POST /api/projects/{id}/snapshots`


## Validation Rules

- Name is required.
- Description is optional but recommended.
- Project type is required.
- Workflow mode is required.
- WordPress version and PHP version should be captured.
- User must be allowed to create projects.
- Entitlements must allow the chosen modules.
- Project must exist before update, archive, duplicate, or status change.
- User must have access to modify the project.
- Snapshot payload must be valid and complete enough for reuse.


## Implementation Notes

- Use a `Project` model for the main record.
- Use `ProjectModule` for add-on attachment.
- Use `ProjectEntitlement` for access and subscription rights.
- Use `ProjectActivityLog` for audit history.
- Use `ProjectSnapshot` for state capture.
- Use a `ProjectStatus` enum or constant map.
- Use request validation classes for all inputs.
- Use policies to enforce access control.
- Store environment snapshots when a project is created or updated.
- Consider using audit logging patterns that track field-level changes and actor identity. [^1][^6][^3][^5]
- Update menu structure when new project screens or settings pages are added.


## Acceptance Criteria

- A user can create a project.
- A user can view and update a project.
- Project status can be changed.
- Modules can be attached and detached.
- Entitlements are enforced.
- Environment metadata is stored.
- Project changes are logged.
- Archived projects are handled correctly.
- Unauthorized access is blocked.
- Project snapshots can be created and reused.
- Project menu items exist and can be updated when needed.


## Next Step

The next module is Feature Spec / Branch Tree. It will use the project type and entitlement context so the AI can build structured branches without guessing.

***

If you want, I can expand **Feature Spec / Branch Tree** next in the same detailed format.
<span style="display:none">[^10][^7][^8][^9]</span>

<div align="center">⁂</div>

[^1]: https://github.com/iamfarhad/laravel-audit-log

[^2]: https://community.atlassian.com/forums/Jira-questions/Workflow-scheme-for-archived-projects/qaq-p/1671300

[^3]: https://www.reddit.com/r/laravel/comments/1etn97u/a_package_for_quick_and_simple_audit_logging_for/

[^4]: https://www.progress.com/documentation/sitefinity-cms/overview-audit-trail-module

[^5]: https://hackernoon.com/how-to-add-a-realtime-audit-trail-to-your-laravel-project-a2fa70411c88

[^6]: https://www.youtube.com/watch?v=8L2Jzo0tVpE

[^7]: https://community.monday.com/learn-workflows-best-practices/post/archiving-cyclic-projects-without-losing-the-board-Zh9X5oe0zqDV9I7

[^8]: https://laravel-news.com/laravel-audit-log

[^9]: https://www.facebook.com/groups/222914377916832/posts/826966980844899/

[^10]: https://pusher.com/tutorials/realtime-audit-trail-laravel/

