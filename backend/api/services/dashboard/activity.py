"""
Activity & Timeline Feed Service for Dashboard.
Exposes chronological project events using existing Project timestamps.
"""
from api.models import Project


def get_dashboard_activity(user):
    is_staff = user.is_staff or user.is_superuser
    proj_qs = Project.objects.all() if is_staff else Project.objects.filter(user=user)

    recent_projects = proj_qs.order_by("-updated_at")[:15]
    events = []

    for p in recent_projects:
        events.append({
            "id": str(p.id),
            "project_name": p.name,
            "event_type": "project_updated",
            "status": p.status,
            "timestamp": p.updated_at.isoformat(),
            "description": p.description[:120] if p.description else "Project updated.",
        })

    return {
        "status": "partially_available",
        "message": "Activity timeline derived from Project update timestamps. Full durable audit log belongs to Module 13 (Activity & Notifications).",
        "events": events,
    }
