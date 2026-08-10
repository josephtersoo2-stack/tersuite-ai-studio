from django.contrib import admin
from .models import SubscriptionPlan, UserSubscription, CreditPurchase

@admin.register(SubscriptionPlan)
class SubscriptionPlanAdmin(admin.ModelAdmin):
    list_display = ["name", "slug", "price_monthly", "is_active"]

@admin.register(UserSubscription)
class UserSubscriptionAdmin(admin.ModelAdmin):
    list_display = ["user", "plan", "payment_gateway", "status", "next_billing"]

@admin.register(CreditPurchase)
class CreditPurchaseAdmin(admin.ModelAdmin):
    list_display = ["user", "amount", "gateway", "status", "created_at"]
