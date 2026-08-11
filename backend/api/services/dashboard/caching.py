"""
User-Isolated Redis Caching Utilities for Dashboard Aggregations.
Enforces strict user boundaries on all cached metrics.
"""
from django.core.cache import cache

DEFAULT_CACHE_TTL = 300  # 5 minutes
HEALTH_CACHE_TTL = 15    # 15 seconds
ACTIVITY_CACHE_TTL = 60  # 1 minute


def build_cache_key(prefix, user, period_label):
    """
    Builds a user-isolated cache key. Staff metrics are keyed under staff scope.
    """
    if user.is_staff or user.is_superuser:
        return f"dashboard:{prefix}:staff:{period_label}"
    return f"dashboard:{prefix}:user:{user.id}:{period_label}"


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
