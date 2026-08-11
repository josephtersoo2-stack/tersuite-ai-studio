"""
Dashboard Response Envelope Serializer Helper.
Constructs standardized JSON envelopes with server timestamps, caching metadata, and latency stats.
"""
import time
from django.utils import timezone


def make_dashboard_envelope(data, cached=False, cache_ttl=0, period="30d", elapsed_ms=0.0):
    """
    Wraps payload in standardized meta/data envelope.

    Args:
        data (dict|list): Payloads
        cached (bool): Whether response was retrieved from Redis cache
        cache_ttl (int): TTL of cached response
        period (str): Period label
        elapsed_ms (float): Execution latency in milliseconds
    """
    return {
        "meta": {
            "generated_at": timezone.now().isoformat(),
            "cached": cached,
            "cache_ttl_seconds": cache_ttl if cached else 0,
            "period": period,
            "query_time_ms": round(elapsed_ms, 2),
        },
        "data": data,
    }
