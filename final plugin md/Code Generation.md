<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# Next

Perfect — next is **Code Generation**. This module turns approved specs and orchestrated sub-agent tasks into actual source files, while keeping the output clean, testable, and aligned with the spec-first workflow. [^1][^2][^3][^4][^5]

***

# Code Generation

## Overview

The Code Generation module turns approved feature specs and agent tasks into actual source files. It is responsible for producing backend code, WordPress plugin code, templates, classes, controllers, services, helpers, and supporting files. It should generate code incrementally from approved specs instead of trying to build everything in one large step. [^1][^2][^3][^4]

## Purpose

- Generate code from structured specs.
- Reuse approved templates and libraries.
- Keep output professional and maintainable.
- Support multiple output types.
- Avoid unnecessary code duplication.
- Produce code in small, verifiable steps.
- Keep generated files aligned with the spec and acceptance criteria. [^1][^6][^2][^3][^4]


## Scope

### Included

- Generate source files.
- Apply templates.
- Insert project-specific values.
- Build folder structures.
- Generate configuration files.
- Generate documentation snippets.
- Generate test stubs where needed.
- Produce diffs and change summaries.
- Support incremental implementation tasks.


### Excluded

- Sandbox execution.
- Packaging and delivery.
- Final release approval.
- Internal template management UI.
- Billing and entitlements.


## Core Entities

- CodeGenerationJob.
- GeneratedFile.
- FileTemplate.
- CodeDiff.
- BuildArtifact.
- GenerationPrompt.
- ImplementationStep.
- OutputManifest.


## Menu Structure

Code generation should be easy to reach from the build area in the backend.

### Suggested menu items

- **Code Generation**
    - Generation Jobs
    - Generate From Spec
    - Generated Files
    - Diffs
    - Output Manifest
    - Templates
    - Generation Settings


### Menu update rule

If a new generation screen, output view, or review panel is added later, the menu item, submenu, or child route must be updated if needed.

## Main Workflows

### create_generation_job

Start a code generation run. The job should be tied to an approved project and feature spec, and it should know what output target it is producing.

### split_into_steps

Break the spec into safe implementation steps. Each step should be small enough for a sub-agent or coder task to complete without ambiguity. [^3][^4]

### render_templates

Apply templates and placeholders to generate files. The template must match the target type and current project context.

### write_files

Create the output files, folder structure, and supporting assets from the rendered templates and implementation steps.

### record_diff

Record what changed in the generated output so reviewers can inspect the delta before acceptance.

### build_output_manifest

Create a manifest of all generated files, their roles, and their dependencies.

### review_generation_output

Compare generated code against the spec, acceptance criteria, and menu impact requirements.

## Generation Path

A clean implementation path should look like this:

1. Read project context.
2. Read approved feature spec.
3. Read the current module goals.
4. Split work into implementation steps.
5. Send the right step to the correct sub-agent or generator.
6. Generate the files.
7. Record diffs.
8. Record the output manifest.
9. Check against acceptance criteria.
10. Update menu items if the generated feature affects navigation.
11. Mark the generation job ready for sandbox or review. [^3][^4]

## Output Rules

- Generate small, focused files.
- Keep file names stable and predictable.
- Never write code that the spec did not request.
- Prefer clarity over cleverness.
- Keep generated code testable.
- Include only the dependencies needed for the target step.
- Produce diffs so reviewers know what changed.
- If UI screens are added, update the menu structure if needed. [^1][^6][^2][^4]


## API Endpoints

- `POST /api/generation/jobs`
- `GET /api/generation/jobs`
- `GET /api/generation/jobs/{jobId}`
- `POST /api/generation/jobs/{jobId}/steps`
- `POST /api/generation/jobs/{jobId}/render`
- `POST /api/generation/jobs/{jobId}/write`
- `GET /api/generation/jobs/{jobId}/files`
- `GET /api/generation/jobs/{jobId}/diff`
- `GET /api/generation/jobs/{jobId}/manifest`
- `POST /api/generation/jobs/{jobId}/review`


## Validation Rules

- Generation can only happen from an approved project and feature spec.
- Every generation job must know its target output.
- File paths must be safe and non-conflicting.
- Templates must exist and match the target type.
- Generated output must be linked to a project and feature.
- Diffs must be stored for review.
- Output manifest must be complete.
- Menu impact must be recorded whenever UI or navigation is affected.


## Implementation Notes

- Separate templates from generated output.
- Use placeholders for project-specific values.
- Keep code generation deterministic where possible.
- Support WordPress and Laravel output patterns.
- Store generated files with metadata and hashes.
- Break generation into incremental tasks instead of one huge prompt.
- Let the orchestrator assign specialized sub-agents to specific generation steps.
- Generate test scaffolding when the spec requires verification.
- Update navigation if the generated code introduces new screens. [^6][^2][^3][^4]


## Acceptance Criteria

- A generation job can be created and run.
- Files are produced from templates.
- Folder structure is valid.
- Diffs are recorded.
- Output manifest exists.
- Output is linked to the correct project and feature.
- Generation can be split into safe steps.
- Menu impact is recorded when required.


## Next Step

The next module is Sandbox and Testing. It will validate the generated code safely before anything is packaged or delivered.

If you want, I can continue with **Sandbox and Testing** next.
<span style="display:none">[^10][^7][^8][^9]</span>

<div align="center">⁂</div>

[^1]: https://www.linkedin.com/posts/kashif-ali-39659518a_laravel-php-webdevelopment-activity-7443586022183063552-cReH

[^2]: https://www.softwareseni.com/specification-templates-for-ai-code-generation-from-first-draft-to-production/

[^3]: https://www.augmentcode.com/tools/best-ai-spec-review-tools-for-development-teams

[^4]: https://www.reddit.com/r/LLMDevs/comments/1p6t3cp/the_spectocode_workflow_building_software_using/

[^5]: https://www.reddit.com/r/node/comments/1dw66d0/the_first_step_toward_a_full_backend_code/

[^6]: https://www.metacto.com/blogs/establishing-code-review-standards-for-ai-generated-code

[^7]: https://github.com/alexeymezenin/laravel-best-practices

[^8]: https://www.reddit.com/r/ClaudeCode/comments/1rg0b9i/has_anyone_tried_the_spec_driven_development/

[^9]: https://www.reddit.com/r/laravel/comments/1pmnfxh/whats_your_goto_approach_for_structuring_large/

[^10]: https://sevalla.com/blog/building-modular-systems-laravel/

