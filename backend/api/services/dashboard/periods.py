"""
Period and Date Range Parsing Service for Dashboard Aggregation.
Provides timezone-aware start, end, and comparison period boundaries.
"""
from datetime import timedelta
from django.utils import timezone
from django.utils.dateparse import parse_datetime


class DashboardPeriodException(ValueError):
    """Raised when an invalid period or malformed date range is provided."""
    pass


def parse_period(period_str="30d", start_date_str=None, end_date_str=None):
    """
    Parses a period parameter or custom ISO date strings into timezone-aware datetime ranges.

    Returns:
        tuple: (current_start, current_end, prev_start, prev_end, period_label)
    """
    now = timezone.now()
    period_str = (period_str or "30d").lower().strip()

    if period_str == "today":
        current_start = now.replace(hour=0, minute=0, second=0, microsecond=0)
        current_end = now
        duration = current_end - current_start
        prev_end = current_start
        prev_start = prev_end - duration
        period_label = "today"

    elif period_str == "7d":
        current_end = now
        current_start = current_end - timedelta(days=7)
        prev_end = current_start
        prev_start = prev_end - timedelta(days=7)
        period_label = "7d"

    elif period_str == "30d":
        current_end = now
        current_start = current_end - timedelta(days=30)
        prev_end = current_start
        prev_start = prev_end - timedelta(days=30)
        period_label = "30d"

    elif period_str == "90d":
        current_end = now
        current_start = current_end - timedelta(days=90)
        prev_end = current_start
        prev_start = prev_end - timedelta(days=90)
        period_label = "90d"

    elif period_str == "ytd":
        current_end = now
        current_start = now.replace(month=1, day=1, hour=0, minute=0, second=0, microsecond=0)
        duration = current_end - current_start
        prev_end = current_start
        prev_start = prev_end - duration
        period_label = "ytd"

    elif period_str == "custom":
        if not start_date_str or not end_date_str:
            raise DashboardPeriodException("Custom period requires start_date and end_date query parameters.")
        
        start_date_str = start_date_str.replace(" ", "+")
        end_date_str = end_date_str.replace(" ", "+")
        current_start = parse_datetime(start_date_str)
        current_end = parse_datetime(end_date_str)

        if not current_start or not current_end:
            raise DashboardPeriodException("Invalid ISO 8601 date format for start_date or end_date.")

        if timezone.is_naive(current_start):
            current_start = timezone.make_aware(current_start)
        if timezone.is_naive(current_end):
            current_end = timezone.make_aware(current_end)

        if current_start >= current_end:
            raise DashboardPeriodException("start_date must be strictly earlier than end_date.")

        duration = current_end - current_start
        if duration.days > 366:
            raise DashboardPeriodException("Custom date ranges cannot exceed 366 days.")

        prev_end = current_start
        prev_start = prev_end - duration
        period_label = "custom"

    else:
        raise DashboardPeriodException(f"Unsupported period '{period_str}'. Allowed: today, 7d, 30d, 90d, ytd, custom.")

    return current_start, current_end, prev_start, prev_end, period_label
