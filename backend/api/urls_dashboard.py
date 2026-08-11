"""
URL Configuration for Dashboard API Endpoints.
"""
from django.urls import path
from . import views_dashboard

urlpatterns = [
    path("overview/", views_dashboard.DashboardOverviewView.as_view(), name="dashboard-overview"),
    path("health/", views_dashboard.DashboardHealthView.as_view(), name="dashboard-health"),
    path("activity/", views_dashboard.DashboardActivityView.as_view(), name="dashboard-activity"),
]
