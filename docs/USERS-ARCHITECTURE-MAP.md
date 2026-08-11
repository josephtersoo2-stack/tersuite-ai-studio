# Tersuite AI Studio — Module 02 Users Architecture Map

**Document Version:** 1.0.0  
**Scope:** Django Auth User Domain, DRF Views, Serializers, Permissions, and Performance Aggregations  
**Rule:** Reuses existing `django.contrib.auth.models.User`, `UserSubscription`, and `Project` models without creating duplicate user domain models.

---

## 1. User Domain Architecture Map

| User Feature / Requirement | Model / Source | Query / Service Logic | API Endpoint | Permissions | Secret Protection |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Paginated User List** | `django.contrib.auth.models.User` | `User.objects.annotate(projects_count=Count('tersuite_projects')).select_related('subscription__plan')` | `GET /api/v1/users/` | Staff / Admin Only | Excludes `password`, `hash`, `tokens` |
| **Current User Profile** | `django.contrib.auth.models.User` | `User.objects.select_related('subscription__plan').get(id=user.id)` | `GET /api/v1/users/me/` | Authenticated User | Excludes `password`, `hash`, `tokens` |
| **User Detail** | `django.contrib.auth.models.User` | `User.objects.annotate(projects_count=Count('tersuite_projects')).get(id=pk)` | `GET /api/v1/users/<id>/` | Staff or Self | Excludes `password`, `hash`, `tokens` |
| **User Profile Update** | `django.contrib.auth.models.User` | Updates `email`, `first_name`, `last_name` | `PATCH /api/v1/users/me/` | Self | Cannot mutate `is_staff` or `is_superuser` |
| **Activate User** | `django.contrib.auth.models.User` | Sets `is_active=True` | `POST /api/v1/users/<id>/activate/` | Staff / Admin Only | Audited action |
| **Deactivate User** | `django.contrib.auth.models.User` | Sets `is_active=False` with self-deactivation safeguards | `POST /api/v1/users/<id>/deactivate/` | Staff / Admin Only | Safeguard: Prevents superuser self-deactivation |

---

## 2. Search, Filter, & Safe Ordering Whitelist

### Searchable Fields
- `username` (icontains)
- `email` (icontains)
- `first_name` (icontains)
- `last_name` (icontains)

### Filterable Parameters
- `is_active` (`true` / `false`)
- `is_staff` (`true` / `false`)
- `joined_after` (ISO 8601 string -> `date_joined__gte`)
- `joined_before` (ISO 8601 string -> `date_joined__lte`)

### Safe Ordering Whitelist
- `id`, `-id`
- `username`, `-username`
- `email`, `-email`
- `date_joined`, `-date_joined`
- `is_active`, `-is_active`
- `projects_count`, `-projects_count`

All invalid or arbitrary ordering strings fall back safely to `-date_joined`.
