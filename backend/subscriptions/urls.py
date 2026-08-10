from django.urls import path
from . import views

urlpatterns = [
    path("plans/", views.PlanListView.as_view(), name="subscription-plans"),
    path("subscribe/", views.SubscribeView.as_view(), name="subscribe"),
    path("status/", views.UserSubscriptionStatusView.as_view(), name="subscription-status"),
    path("purchase-credits/", views.PurchaseCreditsView.as_view(), name="purchase-credits"),
]
