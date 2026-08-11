# Tersuite AI Studio — Django Backend Dashboard Architecture Map

**Document Version:** 1.0.0  
**Scope:** Django Backend Data Authority & Dashboard Metric Mapping  

---

## 1. Executive Summary

This architecture map establishes the exact source of truth for every metric displayed in the **Tersuite AI Studio Dashboard**. Before implementing backend aggregation services or API views, every metric must be traced back to an authoritative Django model, database query, or service layer.

Metrics that lack a database model in the existing backend are explicitly marked as **NOT CURRENTLY AVAILABLE**, along with recommendations for clean implementation without duplicating existing logic.

---

## 2. Complete Metric Architecture Map

### A. Platform & User Overview

| Dashboard Requirement | Existing Django Component | Model / Source | Query / Expression | API Endpoint | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Total Users** | `django.contrib.auth` | `User` | `User.objects.count()` | `GET /api/v1/dashboard/overview/` | **AVAILABLE** |
| **Active Users** | `django.contrib.auth` | `User` | `User.objects.filter(is_active=True).count()` | `GET /api/v1/dashboard/overview/` | **AVAILABLE** |
| **Suspended Users** | `django.contrib.auth` | `User` | `User.objects.filter(is_active=False).count()` | `GET /api/v1/dashboard/overview/` | **AVAILABLE** |
| **New Users Today** | `django.contrib.auth` | `User` | `User.objects.filter(date_joined__date=timezone.now().date()).count()` | `GET /api/v1/dashboard/overview/` | **AVAILABLE** |
| **New Users This Month**| `django.contrib.auth` | `User` | `User.objects.filter(date_joined__month=timezone.now().month).count()` | `GET /api/v1/dashboard/overview/` | **AVAILABLE** |

---

### B. Project Statistics

| Dashboard Requirement | Existing Django Component | Model / Source | Query / Expression | API Endpoint | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Total Projects** | `api` app | `Project` | `Project.objects.count()` | `GET /api/v1/dashboard/projects/` | **AVAILABLE** |
| **Active / Running Projects** | `api` app | `Project` | `Project.objects.filter(status='running').count()` | `GET /api/v1/dashboard/projects/` | **AVAILABLE** |
| **Completed Projects** | `api` app | `Project` | `Project.objects.filter(status='completed').count()` | `GET /api/v1/dashboard/projects/` | **AVAILABLE** |
| **Failed Projects** | `api` app | `Project` | `Project.objects.filter(status='failed').count()` | `GET /api/v1/dashboard/projects/` | **AVAILABLE** |
| **Draft Projects** | `api` app | `Project` | `Project.objects.filter(status='draft').count()` | `GET /api/v1/dashboard/projects/` | **AVAILABLE** |
| **Projects by Category** | `api` app | `Project` + `Category` | `Category.objects.annotate(project_count=Count('projects'))` | `GET /api/v1/dashboard/projects/` | **AVAILABLE** |

---

### C. Subscriptions & Billing

| Dashboard Requirement | Existing Django Component | Model / Source | Query / Expression | API Endpoint | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Active Subscriptions** | `subscriptions` app | `UserSubscription` | `UserSubscription.objects.filter(status='active').count()` | `GET /api/v1/dashboard/billing/` | **AVAILABLE** |
| **Pending Subscriptions**| `subscriptions` app | `UserSubscription` | `UserSubscription.objects.filter(status='pending').count()` | `GET /api/v1/dashboard/billing/` | **AVAILABLE** |
| **Credits Remaining** | `subscriptions` app | `UserSubscription` | `UserSubscription.objects.aggregate(total=Sum('credits_remaining'))` | `GET /api/v1/dashboard/billing/` | **AVAILABLE** |
| **Credit Purchases** | `subscriptions` app | `CreditPurchase` | `CreditPurchase.objects.filter(status='completed')` | `GET /api/v1/dashboard/billing/` | **AVAILABLE** |
| **Revenue / Sales Total** | `subscriptions` app | `CreditPurchase` | `CreditPurchase.objects.filter(status='completed').aggregate(total=Sum('amount'))` | `GET /api/v1/dashboard/billing/` | **AVAILABLE** |

---

### D. LLM Provider & Model Registry

| Dashboard Requirement | Existing Django Component | Model / Source | Query / Expression | API Endpoint | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Configured Providers** | `llm_registry` app | `LLMProvider` | `LLMProvider.objects.filter(enabled=True)` | `GET /api/v1/dashboard/health/` | **AVAILABLE** |
| **Enabled Models** | `llm_registry` app | `LLMModel` | `LLMModel.objects.filter(enabled=True)` | `GET /api/v1/dashboard/health/` | **AVAILABLE** |
| **Default Model** | `llm_registry` app | `LLMModel` | `LLMModel.objects.filter(is_default=True).first()` | `GET /api/v1/dashboard/health/` | **AVAILABLE** |

---

### E. System Health & Infrastructure

| Dashboard Requirement | Existing Django Component | Model / Source | Query / Expression | API Endpoint | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Database Connection** | Django Core | `django.db.connection` | `connection.ensure_connection()` check | `GET /api/v1/dashboard/health/` | **AVAILABLE** |
| **Redis / Cache** | Django Core | `django.core.cache.cache` | `cache.set('ping', 'pong', 5)` ping test | `GET /api/v1/dashboard/health/` | **AVAILABLE** |
| **LLM Provider Ping** | `api.views.LLMTestView` | Google Gemini API Ping | Live HTTP test via `requests.post()` | `GET /api/v1/dashboard/health/` | **AVAILABLE** |
| **Celery Worker Health** | `core.celery` | Celery Inspect | `celery_app.control.inspect().ping()` | `GET /api/v1/dashboard/health/` | **AVAILABLE** |

---

### F. Currently Unavailable Metrics (Requires Model / Audit Notes)

| Dashboard Requirement | Current Status | Reason | Recommendation |
| :--- | :--- | :--- | :--- |
| **Production Plans** | **NOT CURRENTLY AVAILABLE** | No persistent `ProductionPlan` table exists in backend DB. Generations execute via `tasks.py` threading directly. | Implement `ProductionPlan` & `ProductionSession` models in `api.models` to persist plans and user approvals. |
| **Task Graph Execution Log**| **NOT CURRENTLY AVAILABLE** | Task graph progress is currently stored transiently inside `Project.last_result` JSON. | Create `ProductionTask` model to track per-agent progress (`running`, `completed`, `failed`) and dependencies. |
| **Detailed Token Usage** | **NOT CURRENTLY AVAILABLE** | Token counts per generation are logged in stdout/celery output, not aggregated per model in DB. | Add `token_input_count` and `token_output_count` fields to `Project` / `ProductionSession`. |
| **Durable Activity Log** | **NOT CURRENTLY AVAILABLE** | System events are not stored in a central `ActivityLog` model. | Add `ActivityLog` model in `api` app to store events (`project_created`, `plan_approved`, `delivery_ready`). |
| **Package Deliveries** | **PARTIALLY AVAILABLE** | `Project.files` holds generated code JSON, but standalone `DeliveryPackage` table does not exist. | Aggregate completed projects as deliveries in API response until a formal delivery model is added. |

---

## 3. Recommended Backend Architecture & Endpoints

To serve the Dashboard efficiently without breaking existing contracts or introducing N+1 queries, we recommend adding an optimized aggregation view layer in `api/views_dashboard.py`:

* **`GET /api/v1/dashboard/overview/`**: Aggregates total projects, active sessions, completed builds, subscription counts, and usage credits.
* **`GET /api/v1/dashboard/health/`**: Runs fast connection checks across DB, Redis, Gemini/LLM provider, Celery, and WebSocket.
* **`GET /api/v1/dashboard/activity/`**: Returns recent project updates and system events.

All metrics will be derived directly from PostgreSQL using Django ORM aggregation (`Count`, `Sum`, `Filter`) with Redis caching where appropriate.
