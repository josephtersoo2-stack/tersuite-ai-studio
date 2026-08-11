"""
Authoritative Dashboard Overview Aggregation Service.
Aggregates metrics from User, Project, Category, UserSubscription, and CreditPurchase models.
"""
from django.contrib.auth import get_user_model
from django.db.models import Count, Sum, Q
from api.models import Project, Category
from subscriptions.models import UserSubscription, CreditPurchase
from .comparisons import calculate_comparison

User = get_user_model()


def get_dashboard_overview(user, start_dt, end_dt, prev_start_dt, prev_end_dt, period_label):
    is_staff = user.is_staff or user.is_superuser

    # 1. User Metrics (Staff/Admin Only)
    user_metrics = {}
    if is_staff:
        curr_total = User.objects.count()
        curr_active = User.objects.filter(is_active=True).count()
        curr_inactive = User.objects.filter(is_active=False).count()
        curr_new = User.objects.filter(date_joined__gte=start_dt, date_joined__lte=end_dt).count()
        prev_new = User.objects.filter(date_joined__gte=prev_start_dt, date_joined__lt=prev_start_dt + (end_dt - start_dt)).count()

        user_metrics = {
            "total_users": curr_total,
            "active_users": curr_active,
            "inactive_users": curr_inactive,
            "new_users": calculate_comparison(curr_new, prev_new),
        }

    # 2. Project Metrics
    proj_qs = Project.objects.all() if is_staff else Project.objects.filter(user=user)
    curr_proj = proj_qs.filter(created_at__gte=start_dt, created_at__lte=end_dt)
    prev_proj = proj_qs.filter(created_at__gte=prev_start_dt, created_at__lt=prev_start_dt + (end_dt - start_dt))

    total_proj_comp = calculate_comparison(curr_proj.count(), prev_proj.count())

    # Count by status choices: draft, running, testing, completed, failed
    status_counts = proj_qs.aggregate(
        total=Count("id"),
        draft=Count("id", filter=Q(status="draft")),
        running=Count("id", filter=Q(status="running")),
        testing=Count("id", filter=Q(status="testing")),
        completed=Count("id", filter=Q(status="completed")),
        failed=Count("id", filter=Q(status="failed")),
    )

    project_metrics = {
        "total_projects": total_proj_comp,
        "draft_projects": status_counts["draft"],
        "running_projects": status_counts["running"],
        "testing_projects": status_counts["testing"],
        "completed_projects": status_counts["completed"],
        "failed_projects": status_counts["failed"],
    }

    # 3. Category Aggregation Metrics
    if is_staff:
        cat_qs = Category.objects.annotate(project_count=Count("projects"))
    else:
        cat_qs = Category.objects.annotate(project_count=Count("projects", filter=Q(projects__user=user)))

    category_list = []
    for cat in cat_qs.order_by("-project_count", "name"):
        category_list.append({
            "id": str(cat.id),
            "name": cat.name,
            "slug": cat.slug,
            "color": cat.color,
            "project_count": cat.project_count,
        })

    # 4. User Subscription & Credits Metrics
    subscription_metrics = {}
    try:
        sub = UserSubscription.objects.select_related("plan").get(user=user)
        subscription_metrics = {
            "status": sub.status,
            "plan_name": sub.plan.name if sub.plan else "Standard",
            "payment_gateway": sub.payment_gateway,
            "credits_remaining": sub.credits_remaining,
            "next_billing": sub.next_billing.isoformat() if sub.next_billing else None,
        }
    except UserSubscription.DoesNotExist:
        subscription_metrics = {
            "status": "active",
            "plan_name": "Default Trial",
            "payment_gateway": "none",
            "credits_remaining": 100,
            "next_billing": None,
        }

    # 5. Purchases & Revenue Metrics (Staff sees platform revenue)
    purchase_qs = CreditPurchase.objects.filter(status="completed")
    if not is_staff:
        purchase_qs = purchase_qs.filter(user=user)

    curr_purchases = purchase_qs.filter(created_at__gte=start_dt, created_at__lt=end_dt)
    prev_purchases = purchase_qs.filter(created_at__gte=prev_start_dt, created_at__lt=prev_end_dt)

    curr_rev = curr_purchases.aggregate(total=Sum("amount"))["total"] or 0
    prev_rev = prev_purchases.aggregate(total=Sum("amount"))["total"] or 0

    revenue_metrics = {
        "revenue": calculate_comparison(curr_rev, prev_rev) if is_staff else {"value": curr_rev},
        "completed_purchases": curr_purchases.count(),
    }

    # 6. Actionable Attention Alerts
    attention_alerts = []
    if status_counts["failed"] > 0:
        attention_alerts.append({
            "type": "project_failed",
            "severity": "warning",
            "count": status_counts["failed"],
            "message": f"You have {status_counts['failed']} project build(s) in failed state.",
            "action_url": "/projects",
        })

    if subscription_metrics.get("credits_remaining", 0) < 50:
        attention_alerts.append({
            "type": "low_credits",
            "severity": "warning",
            "count": 1,
            "message": "Your credit balance is low (under 50 credits).",
            "action_url": "/subscriptions",
        })

    # 7. Unbuilt Future Module Declarations (Rule 2 Compliance)
    future_modules = {
        "production_plans": {"status": "available_after_later_module", "owning_module": "Module 06 — Production Plan"},
        "production_sessions": {"status": "available_after_later_module", "owning_module": "Module 07 — Production Session"},
        "token_usage_ledger": {"status": "available_after_later_module", "owning_module": "Module 12 — Usage & Subscription"},
        "delivery_packages": {"status": "available_after_later_module", "owning_module": "Module 10 — Deliveries"},
        "sandbox_audit": {"status": "available_after_later_module", "owning_module": "Module 11 — Site Integration"},
    }

    return {
        "users": user_metrics if is_staff else {"status": "restricted_to_staff"},
        "projects": project_metrics,
        "categories": category_list,
        "subscription": subscription_metrics,
        "revenue": revenue_metrics,
        "attention": attention_alerts,
        "future_modules": future_modules,
    }
