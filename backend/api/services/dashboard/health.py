"""
Infrastructure Health & Connectivity Diagnostic Service for Dashboard.
Evaluates PostgreSQL, Redis, LLM Registry, Channels, and Celery without exposing secrets.
"""
import os
from django.db import connection
from django.core.cache import cache
from llm_registry.models import LLMProvider, LLMModel


def check_database_health():
    try:
        with connection.cursor() as cursor:
            cursor.execute("SELECT 1")
            cursor.fetchone()
        return {
            "status": "healthy",
            "engine": connection.vendor,
        }
    except Exception as e:
        return {
            "status": "unhealthy",
            "error": "Database connection failed",
        }


def check_redis_health():
    test_key = "tsa_health_ping_test"
    try:
        cache.set(test_key, "pong", 5)
        val = cache.get(test_key)
        cache.delete(test_key)
        if val == "pong":
            return {
                "status": "healthy",
                "backend": "redis_cache",
            }
        return {
            "status": "degraded",
            "reason": "Cache read mismatch",
        }
    except Exception as e:
        return {
            "status": "unhealthy",
            "error": "Cache service unreachable",
        }


def check_llm_providers_health():
    providers = []
    try:
        active_providers = LLMProvider.objects.filter(enabled=True).order_by("order")
        for p in active_providers:
            env_var = p.api_key_env_var
            is_configured = bool(os.getenv(env_var)) if env_var else False
            providers.append({
                "id": p.id,
                "name": p.name,
                "display_name": p.display_name,
                "enabled": p.enabled,
                "credentials_configured": is_configured,
                "status": "configured" if is_configured else "missing_api_key",
            })
    except Exception as e:
        providers = []

    return providers


def check_llm_models_registry():
    models = []
    try:
        active_models = LLMModel.objects.filter(enabled=True).select_related("provider")
        for m in active_models:
            models.append({
                "id": m.id,
                "model_id": m.model_id,
                "display_name": m.display_name,
                "provider_name": m.provider.name,
                "is_default": m.is_default,
                "max_tokens": m.max_tokens,
            })
    except Exception as e:
        models = []

    return models


def check_channels_health():
    try:
        from channels.layers import get_channel_layer
        layer = get_channel_layer()
        if layer is not None:
            return {
                "status": "partially_available",
                "layer_backend": layer.__class__.__name__,
                "message": "Channel layer readiness verified. Active client connection count tracking requires WebSocket connection manager.",
            }
        return {
            "status": "unhealthy",
            "error": "No channel layer configured",
        }
    except Exception as e:
        return {
            "status": "partially_available",
            "error": "Django Channels module uninitialized",
        }


def check_celery_health():
    return {
        "status": "requires_new_infrastructure",
        "message": "Celery worker inspect requires separate worker broker monitoring infrastructure.",
    }


def get_system_health():
    db_status = check_database_health()
    redis_status = check_redis_health()
    providers = check_llm_providers_health()
    models = check_llm_models_registry()
    channels_status = check_channels_health()
    celery_status = check_celery_health()

    overall = "healthy"
    if db_status.get("status") == "unhealthy" or redis_status.get("status") == "unhealthy":
        overall = "unhealthy"
    elif any(p.get("status") == "missing_api_key" for p in providers):
        overall = "degraded"

    return {
        "overall_status": overall,
        "database": db_status,
        "redis": redis_status,
        "llm_providers": providers,
        "llm_models": models,
        "channels": channels_status,
        "celery": celery_status,
    }
