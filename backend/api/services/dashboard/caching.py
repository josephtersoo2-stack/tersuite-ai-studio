"""
User-Isolated Redis Caching Utilities for Dashboard Aggregations.
Enforces strict user boundaries on all cached metrics.
"""
from django.core.cache import cache

DEFAULT_CACHE_TTL = 300  # 5 minutes
HEALTH_CACHE_TTL = 15    # 15 seconds
ACTIVITY_CACHE_TTL = 60  # 1 minute


def build_cache_key(prefix, user, period_label, start_dt=None, end_dt=None):
    """
    Builds a user-isolated cache key. Staff metrics are keyed under staff scope.
    For custom periods, includes normalized ISO start/end timestamps to avoid cache collisions.
    """
    if period_label == "custom" and start_dt and end_dt:
        s_str = start_dt.strftime("%Y%m%d%H%M%S")
        e_str = end_dt.strftime("%Y%m%d%H%M%S")
        suffix = f"custom:{s_str}_{e_str}"
    else:
        suffix = period_label

    if user.is_staff or user.is_superuser:
        return f"dashboard:{prefix}:staff:{suffix}"
    return f"dashboard:{prefix}:user:{user.id}:{suffix}"


def get_cached_dashboard_data(cache_key):
    """Retrieves cached dashboard payload or None."""
    try:
        return cache.get(cache_key)
    except Exception:
        return None


def set_cached_dashboard_data(cache_key, data, ttl=DEFAULT_CACHE_TTL):
    """Stores dashboard payload in Redis/Django cache with a defined TTL."""
    try:
        cache.set(cache_key, data, timeout=ttl)
    except Exception:
        pass
