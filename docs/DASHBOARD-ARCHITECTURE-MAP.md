# Tersuite AI Studio — Revised Dashboard Architecture Map

**Document Version:** 2.0.0 (Revised Post-Audit)  
**Scope:** Authoritative Data Source Mapping, Availability Classification, and Aggregation Architecture  
**Rule:** Dashboard is an observability and aggregation layer. No core business domain models or business logic may be prematurely created inside Module 01.  

---

## 1. Architectural Principles & Constraints

1. **Pure Aggregation Layer**: The Dashboard consumes authoritative data from existing Django models and services. It does not own primary domain state.
2. **Zero Premature Model Creation**: The following models belong to future modules and MUST NOT be created in Module 01:
   - `ProductionPlan` (Module 06 — Production Plan)
   - `ProductionSession` (Module 07 — Production Session)
   - `ProductionTask` (Module 06 / 07 — Task Graph & Sessions)
   - `AIUsageRecord` / Token Ledger (Module 12 — Usage & Subscription)
   - `ActivityLog` (Module 13 — Activity & Notifications)
   - `DeliveryPackage` (Module 10 — Deliveries)
3. **Exact Field & Choice Fidelity**: Metrics must strictly reflect the actual Django models in the codebase:
   - `Project.status` choices: `draft`, `running`, `testing`, `completed`, `failed`.
   - `UserSubscription.status` choices: `active`, `pending`, `cancelled`.
   - `User.is_active=False` represents **Inactive Users** (the model does not explicitly define suspension).
4. **Timezone-Aware Date Ranges**: All date filtering must use timezone-aware datetime boundaries (`timezone.now()`) to prevent multi-year range leaks.
5. **Provider-Agnostic Infrastructure Diagnostics**: LLM provider diagnostics query the `llm_registry` app dynamically and verify configured environment variables across all active providers without hardcoding Google Gemini or OpenAI.
6. **Factual Realtime / WebSocket Diagnostics**: Realtime connection health is evaluated by pinging the Django Channels Redis channel layer via `channels.layers.get_channel_layer()`. No client connection health is fabricated.

---

## 2. Global Metric Classification Matrix

Every proposed Dashboard metric is classified under one of five strict availability statuses:
- **`AVAILABLE_NOW`**: Fully supported by existing Django models/services in the codebase.
- **`PARTIALLY_AVAILABLE`**: Supported via existing model fields, but lacks historical trend tables or sub-field breakdowns.
- **`AVAILABLE_AFTER_LATER_MODULE`**: Requires models/services to be built in a designated future module.
- **`REQUIRES_NEW_INFRASTRUCTURE`**: Requires external infrastructure integration (e.g., Celery Redis inspect, sandbox daemon).
- **`NOT_SUPPORTED`**: Excluded from scope as no authoritative underlying data source exists or will exist.

---

## 3. Comprehensive Architecture Map Table

| Metric | Source Model / Service | Query / Service Logic | Current Availability | Future Owning Module | API Endpoint | Cache Requirements | Time-Range Support | Permission Requirements | Security Sensitivity |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Total Users** | `django.contrib.auth.models.User` | `User.objects.count()` | `AVAILABLE_NOW` | Module 02 — Users | `GET /api/v1/dashboard/overview/` | 300s Redis Cache | No | Staff / Admin | Low |
| **Active Users** | `django.contrib.auth.models.User` | `User.objects.filter(is_active=True).count()` | `AVAILABLE_NOW` | Module 02 — Users | `GET /api/v1/dashboard/overview/` | 300s Redis Cache | No | Staff / Admin | Low |
| **Inactive Users** | `django.contrib.auth.models.User` | `User.objects.filter(is_active=False).count()` | `AVAILABLE_NOW` | Module 02 — Users | `GET /api/v1/dashboard/overview/` | 300s Redis Cache | No | Staff / Admin | Low |
| **New Users (Range)** | `django.contrib.auth.models.User` | `User.objects.filter(date_joined__gte=start, date_joined__lt=end).count()` | `AVAILABLE_NOW` | Module 02 — Users | `GET /api/v1/dashboard/overview/` | 300s Redis Cache | Yes (`today`, `7d`, `30d`, `90d`, `ytd`, `custom`) | Staff / Admin | Low |
| **Total Projects** | `api.models.Project` | `Project.objects.filter(user=user).count()` | `AVAILABLE_NOW` | Module 03 — Projects | `GET /api/v1/dashboard/overview/` | No Cache (Realtime) | Yes (`today`, `7d`, `30d`, `90d`, `ytd`, `custom`) | Authenticated User | Low |
| **Projects (Draft)** | `api.models.Project` | `Project.objects.filter(user=user, status='draft').count()` | `AVAILABLE_NOW` | Module 03 — Projects | `GET /api/v1/dashboard/overview/` | No Cache (Realtime) | Yes | Authenticated User | Low |
| **Projects (Running)** | `api.models.Project` | `Project.objects.filter(user=user, status='running').count()` | `AVAILABLE_NOW` | Module 03 — Projects | `GET /api/v1/dashboard/overview/` | No Cache (Realtime) | Yes | Authenticated User | Low |
| **Projects (Testing)** | `api.models.Project` | `Project.objects.filter(user=user, status='testing').count()` | `AVAILABLE_NOW` | Module 03 — Projects | `GET /api/v1/dashboard/overview/` | No Cache (Realtime) | Yes | Authenticated User | Low |
| **Projects (Completed)**| `api.models.Project` | `Project.objects.filter(user=user, status='completed').count()` | `AVAILABLE_NOW` | Module 03 — Projects | `GET /api/v1/dashboard/overview/` | No Cache (Realtime) | Yes | Authenticated User | Low |
| **Projects (Failed)** | `api.models.Project` | `Project.objects.filter(user=user, status='failed').count()` | `AVAILABLE_NOW` | Module 03 — Projects | `GET /api/v1/dashboard/overview/` | No Cache (Realtime) | Yes | Authenticated User | Low |
| **Projects by Category** | `api.models.Category` & `Project` | `Category.objects.annotate(count=Count('projects', filter=Q(projects__user=user)))` | `AVAILABLE_NOW` | Module 03 — Projects | `GET /api/v1/dashboard/overview/` | 60s Redis Cache | No | Authenticated User | Low |
| **Active Subscription** | `subscriptions.models.UserSubscription` | `UserSubscription.objects.get(user=user)` | `AVAILABLE_NOW` | Module 12 — Usage & Subscription | `GET /api/v1/dashboard/overview/` | 60s Redis Cache | No | Authenticated User | Medium |
| **Credits Remaining** | `subscriptions.models.UserSubscription` | `sub.credits_remaining` | `AVAILABLE_NOW` | Module 12 — Usage & Subscription | `GET /api/v1/dashboard/overview/` | No Cache (Realtime) | No | Authenticated User | Medium |
| **Credit Purchases** | `subscriptions.models.CreditPurchase` | `CreditPurchase.objects.filter(user=user, status='completed')` | `AVAILABLE_NOW` | Module 12 — Usage & Subscription | `GET /api/v1/dashboard/overview/` | 60s Redis Cache | Yes | Authenticated User | Sensitive |
| **Revenue Total** | `subscriptions.models.CreditPurchase` | `CreditPurchase.objects.filter(status='completed').aggregate(total=Sum('amount'))` | `AVAILABLE_NOW` | Module 12 — Usage & Subscription | `GET /api/v1/dashboard/overview/` | 300s Redis Cache | Yes | Staff / Admin | High (Confidential) |
| **LLM Providers Status**| `llm_registry.models.LLMProvider` | `LLMProvider.objects.filter(enabled=True)` + `os.getenv(env_var)` check | `AVAILABLE_NOW` | Module 14 — Settings & System | `GET /api/v1/dashboard/health/` | 60s Redis Cache | No | Authenticated User | Medium (Hide Keys) |
| **LLM Models Registry** | `llm_registry.models.LLMModel` | `LLMModel.objects.filter(enabled=True).select_related('provider')` | `AVAILABLE_NOW` | Module 14 — Settings & System | `GET /api/v1/dashboard/health/` | 300s Redis Cache | No | Authenticated User | Low |
| **Database Health** | `django.db.connection` | Execute `SELECT 1` with connection pool status | `AVAILABLE_NOW` | Module 14 — Settings & System | `GET /api/v1/dashboard/health/` | No Cache (Realtime) | No | Authenticated User | Low |
| **Redis Cache Health** | `django.core.cache.cache` | Execute test write/read `cache.set('ping', 'pong', 5)` | `AVAILABLE_NOW` | Module 14 — Settings & System | `GET /api/v1/dashboard/health/` | No Cache (Realtime) | No | Authenticated User | Low |
| **WebSocket Layer Health**| `channels.layers` | Inspect `get_channel_layer()` connection readiness | `PARTIALLY_AVAILABLE` | Module 14 — Settings & System | `GET /api/v1/dashboard/health/` | No Cache (Realtime) | No | Authenticated User | Low |
| **Celery Queue Depth** | Celery Broker Inspect | `celery_app.control.inspect().active()` | `REQUIRES_NEW_INFRASTRUCTURE` | Module 14 — Settings & System | `GET /api/v1/dashboard/health/` | 15s Redis Cache | No | Staff / Admin | Medium |
| **Production Plans** | Nonexistent in DB | Requires `ProductionPlan` table | `AVAILABLE_AFTER_LATER_MODULE` | Module 06 — Production Plan | `GET /api/v1/dashboard/overview/` | N/A | N/A | N/A | N/A |
| **Production Sessions** | Nonexistent in DB | Requires `ProductionSession` table | `AVAILABLE_AFTER_LATER_MODULE` | Module 07 — Production Session | `GET /api/v1/dashboard/overview/` | N/A | N/A | N/A | N/A |
| **Token Usage Ledger** | Nonexistent in DB | Requires `TokenUsageRecord` table | `AVAILABLE_AFTER_LATER_MODULE` | Module 12 — Usage & Subscription | `GET /api/v1/dashboard/overview/` | N/A | N/A | N/A | N/A |
| **Durable Activity Log** | Nonexistent in DB | Requires `ActivityLog` table | `AVAILABLE_AFTER_LATER_MODULE` | Module 13 — Activity & Notifications | `GET /api/v1/dashboard/activity/` | N/A | N/A | N/A | N/A |
| **Delivery Packages** | Derived from `Project.files` | Derived from completed project JSON | `PARTIALLY_AVAILABLE` | Module 10 — Deliveries | `GET /api/v1/dashboard/overview/` | 60s Redis Cache | Yes | Authenticated User | Low |
| **Sandbox Execution Audit**| Nonexistent in DB | Requires `SandboxReport` table | `AVAILABLE_AFTER_LATER_MODULE` | Module 11 — Site Integration | `GET /api/v1/dashboard/health/` | N/A | N/A | N/A | N/A |

---

## 4. Time-Range Filtering Architecture

To ensure consistent date filtering across user stats, project creation rates, and revenue metrics without preventing future custom range queries, all aggregation queries must accept standard query parameters:

```text
GET /api/v1/dashboard/overview/?period=30d
GET /api/v1/dashboard/overview/?start_date=2026-08-01T00:00:00Z&end_date=2026-08-11T23:59:59Z
```

### Supported Period Identifiers:
1. `today`: From `timezone.now().replace(hour=0, minute=0, second=0, microsecond=0)` to `now()`.
2. `7d`: From `now() - timedelta(days=7)` to `now()`.
3. `30d` (Default): From `now() - timedelta(days=30)` to `now()`.
4. `90d`: From `now() - timedelta(days=90)` to `now()`.
5. `ytd`: From `now().replace(month=1, day=1, hour=0, minute=0, second=0)` to `now()`.
6. `custom`: Evaluates ISO 8601 strings provided in `start_date` and `end_date`.

---

## 5. Metric Comparison & Trend Contract Architecture

Metrics with comparison support must return a standardized structured comparison object. Values must never be fabricated; if previous period data is unavailable, `previous_value` and `change_percent` return `null`.

```json
{
  "total_projects": {
    "value": 24,
    "previous_value": 18,
    "change_percent": 33.33,
    "trend": "up"
  }
}
```

### Calculation Rules:
* `change_percent = ((value - previous_value) / previous_value) * 100` (when `previous_value > 0`).
* `trend` values: `"up"` (positive change), `"down"` (negative change), `"neutral"` (zero change or null).

---

## 6. Freshness & Caching Envelope Standard

All Dashboard API responses must be wrapped in a standardized envelope providing explicit metadata regarding generation time, caching state, and execution latency:

```json
{
  "meta": {
    "generated_at": "2026-08-11T22:10:00.123456Z",
    "cached": false,
    "cache_ttl_seconds": 0,
    "period": "30d",
    "query_time_ms": 14.2
  },
  "data": {
    "overview": {},
    "projects": {},
    "usage": {},
    "attention": []
  }
}
```

---

## 7. Versioned API Endpoint Boundaries

The Dashboard API is strictly bounded to three consolidated aggregation endpoints under `/api/v1/dashboard/`:

1. **`GET /api/v1/dashboard/overview/`**
   - Consolidates: User stats (staff), Project counts by status/category, User Subscription & Credits, Revenue summary (staff), and Actionable Alerts.
2. **`GET /api/v1/dashboard/health/`**
   - Consolidates: Infrastructure status for PostgreSQL DB, Redis Cache, dynamic LLM Providers registry check, and Django Channels Redis layer.
3. **`GET /api/v1/dashboard/activity/`**
   - Consolidates: Chronological project timeline feed based on actual `Project.updated_at` records.

---

## 8. Security, Isolation & Sensitivity Rules

1. **Authentication Guard**: All endpoints require `rest_framework.permissions.IsAuthenticated`.
2. **Data Isolation**: Non-staff users only receive metrics for projects where `user = request.user`. Staff members (`is_staff=True`) receive platform-wide totals.
3. **Secret Masking**: `api_base_url` and `api_key_env_var` names may be exposed to admins, but raw API key values, environment variable secret strings, database passwords, and auth tokens are **STRICTLY EXCLUDED** from all response serializers.
