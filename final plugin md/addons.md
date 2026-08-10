<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# Add-ons / Modules / Entitlements

**Add-ons / Modules / Entitlements** controls optional capabilities, module activation, and access rights through a centralized entitlement layer. The key design rule is to keep entitlement checks in one shared place so access logic stays consistent and maintainable. [^1][^2][^3]

## Overview

This module manages what extra features exist, which ones are installed, and who can use them. It supports plan-based access, role-based access, module dependencies, usage limits, and license status. [^1][^2][^3]

## Purpose

- Gate optional features.
- Enable and disable modules.
- Assign access by tenant, plan, role, or license.
- Enforce usage limits.
- Track license and entitlement status.
- Keep access logic centralized.
- Support modular product growth. [^1][^2][^3]


## Core Entities

- AddOn.
- Module.
- Entitlement.
- Plan.
- FeatureFlag.
- AccessRule.
- TenantAccess.
- LicenseRecord.


## Main Workflows

- `register_addon`.
- `assign_entitlement`.
- `evaluate_access`.
- `enable_module`.
- `revoke_entitlement`.


## Rules

- Entitlement checks must be centralized. [^1]
- Module dependencies must be respected.
- Disabled or expired entitlements must block access.
- Access decisions should be auditable.
- Feature flags may override or refine access where appropriate.


## Menu Items

- Available Add-ons.
- Installed Modules.
- Entitlements.
- Feature Flags.
- Plans.
- Access Rules.
- Usage Limits.
- License Status.


## API Endpoints

- `POST /api/addons`
- `GET /api/addons`
- `GET /api/addons/{addonId}`
- `POST /api/modules`
- `GET /api/modules`
- `POST /api/entitlements`
- `GET /api/entitlements`
- `POST /api/entitlements/evaluate`
- `PATCH /api/entitlements/{entitlementId}`
- `POST /api/feature-flags`
- `GET /api/feature-flags`
- `POST /api/access-rules`


## Acceptance Criteria

- Add-ons can be registered.
- Modules can be enabled and disabled.
- Entitlements can be assigned and evaluated.
- Access checks are centralized.
- Expired entitlements are denied.
- Module activation respects access rules.

Would you like the next module to be **API, Security, or Billing**?

<div align="center">⁂</div>

[^1]: https://www.stigg.io/blog-posts/entitlements-untangled-the-modern-way-to-software-monetization

[^2]: https://www.reddit.com/r/softwarearchitecture/comments/1ujlsqs/best_practices_for_developing_massive_extensible/

[^3]: https://www.wondermentapps.com/blog/software-architecture-best-practices/

