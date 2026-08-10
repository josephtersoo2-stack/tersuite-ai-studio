<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# Yes, proceed with the Sandbox and Testing module next

Yes — next is **Sandbox and Testing**. This module is where generated code gets executed and validated in isolation before anything is packaged or released, which is exactly what you want for safe AI-assisted development. [^1][^2][^3][^4][^5]

***

# Sandbox and Testing

## Overview

The Sandbox and Testing module runs generated code in isolated environments before release. It checks compatibility, catches errors, and verifies that the generated output behaves correctly for the target WordPress and PHP versions. The sandbox should be disposable, limited, and separate from production artifacts. [^1][^2][^3][^4][^5]

## Purpose

- Validate generated code safely.
- Prevent broken builds from reaching users.
- Run tests in isolated environments.
- Capture logs and failures.
- Support multiple environment profiles.
- Mirror production versions as closely as practical.
- Keep test code outside production artifacts. [^1][^2][^3][^4][^5]


## Scope

### Included

- Sandbox creation.
- Sandbox execution.
- Test running.
- Error capture.
- Compatibility checks.
- Result reporting.
- Resource limits.
- Reset and destroy behavior.
- Test artifact storage.


### Excluded

- Code generation itself.
- Packaging and delivery.
- UI design.
- Billing and entitlements.
- Production deployment.


## Core Entities

- SandboxEnvironment.
- SandboxRun.
- TestCase.
- TestResult.
- CompatibilityReport.
- SandboxLog.
- ResourcePolicy.
- ExecutionTrace.


## Menu Structure

The sandbox area should be reachable from builds or testing in the admin shell.

### Suggested menu items

- **Sandbox and Testing**
    - Sandbox Environments
    - Test Runs
    - Compatibility Checks
    - Logs
    - Results
    - Policies


### Menu update rule

If a new test view, results screen, or sandbox policy page is added later, the menu item, submenu, or child route must be updated if needed.

## Main Workflows

### create_sandbox

Provision an isolated environment. The sandbox should be tied to a project, feature, or generation job and should use the correct target versions.

### run_tests

Execute the test suite in the sandbox. The code should be run in a disposable environment with limits on CPU, memory, and runtime.

### check_compatibility

Verify compatibility with target WordPress, PHP, plugin, or theme versions.

### collect_logs

Store sandbox logs and traces for later review and debugging.

### destroy_sandbox

Destroy the sandbox when the run is done or times out so nothing unnecessary persists.

### reset_sandbox

Recreate a clean environment for reruns.

## Sandbox Rules

- Sandbox must not be production.
- Sandbox should be isolated from host files and unrelated systems.
- Only the project directory or approved workspace should be mounted.
- Network access should be restricted unless explicitly allowed.
- Sandboxes should have resource limits.
- Sandboxes should be disposable after use.
- Test code should not ship inside the production artifact. [^1][^2][^3][^4][^5]


## Testing Rules

- Tests must match the target project type.
- Basic validation should happen before deep execution.
- Failed tests must produce clear failure output.
- Compatibility results must be stored.
- Test suites should run before packaging.
- Repeatable tests should be rerunnable in a clean sandbox.
- Long outputs should be summarized for review.
- Errors should be placed near the code or step that caused them. [^1][^2][^6][^5]


## Execution Path

A clean implementation path should look like this:

1. Read the approved project and feature spec.
2. Read the generated code manifest.
3. Create a sandbox from the correct target profile.
4. Mount only the approved project workspace.
5. Run tests and compatibility checks.
6. Capture logs, traces, and results.
7. Summarize pass/fail status.
8. Destroy or reset the sandbox.
9. Send failed outputs back for correction.
10. Update menu items if a new testing screen is needed. [^3][^4][^5]

## API Endpoints

- `POST /api/sandboxes`
- `GET /api/sandboxes`
- `GET /api/sandboxes/{sandboxId}`
- `POST /api/sandboxes/{sandboxId}/run`
- `POST /api/sandboxes/{sandboxId}/tests`
- `POST /api/sandboxes/{sandboxId}/compatibility-check`
- `GET /api/sandboxes/{sandboxId}/results`
- `GET /api/sandboxes/{sandboxId}/logs`
- `POST /api/sandboxes/{sandboxId}/reset`
- `DELETE /api/sandboxes/{sandboxId}`


## Validation Rules

- Sandbox must match the project’s target environment.
- Generated code must exist before testing.
- Test results must be stored.
- Failures must be clearly reported.
- Sandbox access must be restricted.
- Resource policies must be enforced.
- Sandboxes must be destroyed or reset after use.
- Production artifacts must not contain test code. [^1][^2][^3][^4][^5]


## Implementation Notes

- Use isolated containers or equivalent execution environments.
- Keep environment profiles reusable.
- Store test cases separately from runtime logs.
- Preserve compatibility reports for later review.
- Allow reruns after fixes.
- Apply strict filesystem and network restrictions.
- Mount only approved workspace paths.
- Keep the sandbox ephemeral and disposable. [^3][^4][^5]


## Acceptance Criteria

- A sandbox can be created for a project.
- Tests can run inside the sandbox.
- Compatibility is checked.
- Logs are captured.
- Test results determine whether the build can advance.
- Resource limits and isolation rules are applied.
- Sandbox cleanup happens automatically or through controlled reset.
- Menu items exist for sandbox and testing screens when needed.


## Next Step

The next module is Packaging and Delivery. It will collect validated output and prepare it for release.

If you want, I can continue with **Packaging and Delivery** next.
<span style="display:none">[^10][^7][^8][^9]</span>

<div align="center">⁂</div>

[^1]: https://www.reddit.com/r/softwarearchitecture/comments/1ppruoe/best_practices_for_implementing_a_sandboxtest/

[^2]: https://www.docsie.io/blog/glossary/code-sandbox/

[^3]: https://www.koyeb.com/blog/why-you-should-run-ai-generated-code-in-a-sandbox

[^4]: https://www.bunnyshell.com/guides/sandboxed-environments-ai-coding/

[^5]: https://mbrenndoerfer.com/writing/code-execution-sandboxed-feedback-iterative-refinement-safety

[^6]: https://www.signadot.com/blog/sandbox-testing-the-devex-game-changer-for-microservices/

[^7]: https://dev.to/signadot/microservices-testing-4-use-cases-for-sandbox-environments-575k

[^8]: https://stackoverflow.com/questions/55046707/sharing-backend-and-frontend-input-validation-or-module

[^9]: https://arxiv.org/html/2603.03394v1

[^10]: https://learn.microsoft.com/en-us/azure/automation/shared-resources/modules

