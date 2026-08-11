"""
User Query & Search Service Layer for Module 02 Users.
Optimizes queries using annotate and select_related while enforcing a safe ordering whitelist.
"""
from django.contrib.auth import get_user_model
from django.db.models import Count, Q
from django.utils.dateparse import parse_datetime

User = get_user_model()

SAFE_ORDERING_WHITELIST = {
    "id": "id",
    "-id": "-id",
    "username": "username",
    "-username": "-username",
    "email": "email",
    "-email": "-email",
    "date_joined": "date_joined",
    "-date_joined": "-date_joined",
    "is_active": "is_active",
    "-is_active": "-is_active",
    "projects_count": "projects_count",
    "-projects_count": "-projects_count",
}


def build_user_queryset(query_params):
    """
    Constructs an optimized user queryset with search, filters, and safe ordering.
    """
    qs = User.objects.annotate(
        projects_count=Count("tersuite_projects")
    ).select_related("subscription__plan")

    # Search filter
    search_q = query_params.get("search", "").strip()
    if search_q:
        qs = qs.filter(
            Q(username__icontains=search_q) |
            Q(email__icontains=search_q) |
            Q(first_name__icontains=search_q) |
            Q(last_name__icontains=search_q)
        )

    # Status filters
    is_active = query_params.get("is_active")
    if is_active is not None:
        if is_active.lower() in ["true", "1"]:
            qs = qs.filter(is_active=True)
        elif is_active.lower() in ["false", "0"]:
            qs = qs.filter(is_active=False)

    is_staff = query_params.get("is_staff")
    if is_staff is not None:
        if is_staff.lower() in ["true", "1"]:
            qs = qs.filter(is_staff=True)
        elif is_staff.lower() in ["false", "0"]:
            qs = qs.filter(is_staff=False)

    # Date filters
    joined_after = query_params.get("joined_after")
    if joined_after:
        dt = parse_datetime(joined_after.replace(" ", "+"))
        if dt:
            qs = qs.filter(date_joined__gte=dt)

    joined_before = query_params.get("joined_before")
    if joined_before:
        dt = parse_datetime(joined_before.replace(" ", "+"))
        if dt:
            qs = qs.filter(date_joined__lte=dt)

    # Safe ordering whitelist
    ordering_param = query_params.get("ordering", "-date_joined").strip()
    db_ordering = SAFE_ORDERING_WHITELIST.get(ordering_param, "-date_joined")

    return qs.order_by(db_ordering)
