"""
Django REST Framework Views for the Tersuite AI Studio Dashboard.
Exposes Overview, Health, and Activity endpoints with envelope wrapping, permission checks, and Redis caching.
"""
import time
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework.permissions import IsAuthenticated
from rest_framework import status

from .serializers_dashboard import make_dashboard_envelope
from .services.dashboard import (
    parse_period,
    DashboardPeriodException,
    build_cache_key,
    get_cached_dashboard_data,
    set_cached_dashboard_data,
    DEFAULT_CACHE_TTL,
    get_system_health,
    get_dashboard_activity,
    get_dashboard_overview,
)


class DashboardOverviewView(APIView):
    """
    Overview Aggregation Endpoint.
    Returns User stats (staff), Project metrics, Subscription/Credits, Revenue (staff), and Attention Alerts.
    """
    permission_classes = [IsAuthenticated]

    def get(self, request):
        start_time = time.time()
        period = request.query_params.get("period", "30d")
        start_date = request.query_params.get("start_date")
        end_date = request.query_params.get("end_date")

        try:
            start_dt, end_dt, prev_start_dt, prev_end_dt, period_label = parse_period(
                period_str=period,
                start_date_str=start_date,
                end_date_str=end_date
            )
        except DashboardPeriodException as e:
            return Response({"error": str(e)}, status=status.HTTP_400_BAD_REQUEST)

        # User-isolated cache lookup
        cache_key = build_cache_key("overview", request.user, period_label, start_dt, end_dt)
        cached_payload = get_cached_dashboard_data(cache_key)

        if cached_payload is not None:
            elapsed_ms = (time.time() - start_time) * 1000
            return Response(make_dashboard_envelope(
                data=cached_payload,
                cached=True,
                cache_ttl=DEFAULT_CACHE_TTL,
                period=period_label,
                elapsed_ms=elapsed_ms
            ))

        # Query & aggregate
        data = get_dashboard_overview(
            user=request.user,
            start_dt=start_dt,
            end_dt=end_dt,
            prev_start_dt=prev_start_dt,
            prev_end_dt=prev_end_dt,
            period_label=period_label
        )

        set_cached_dashboard_data(cache_key, data, ttl=DEFAULT_CACHE_TTL)
        elapsed_ms = (time.time() - start_time) * 1000

        return Response(make_dashboard_envelope(
            data=data,
            cached=False,
            period=period_label,
            elapsed_ms=elapsed_ms
        ))


class DashboardHealthView(APIView):
    """
    Infrastructure Diagnostic Health Endpoint.
    Monitors DB, Redis, LLM Providers, LLM Models, Channels, and Celery without exposing secrets.
    """
    permission_classes = [IsAuthenticated]

    def get(self, request):
        start_time = time.time()
        health_data = get_system_health()
        elapsed_ms = (time.time() - start_time) * 1000

        return Response(make_dashboard_envelope(
            data=health_data,
            cached=False,
            period="realtime",
            elapsed_ms=elapsed_ms
        ))


class DashboardActivityView(APIView):
    """
    Recent Activity Timeline Endpoint.
    Returns chronological timeline events derived from Project updates.
    """
    permission_classes = [IsAuthenticated]

    def get(self, request):
        start_time = time.time()
        activity_data = get_dashboard_activity(request.user)
        elapsed_ms = (time.time() - start_time) * 1000

        return Response(make_dashboard_envelope(
            data=activity_data,
            cached=False,
            period="realtime",
            elapsed_ms=elapsed_ms
        ))
