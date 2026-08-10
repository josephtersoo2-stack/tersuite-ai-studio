from django.db import models
from django.contrib.auth import get_user_model

User = get_user_model()


class SubscriptionPlan(models.Model):
    name = models.CharField(max_length=100)
    slug = models.CharField(max_length=50, unique=True)
    price_monthly = models.DecimalField(max_digits=10, decimal_places=2)
    generations_limit = models.PositiveIntegerField(default=10)
    features = models.TextField(default="Standard pipeline, email support")
    is_active = models.BooleanField(default=True)


class UserSubscription(models.Model):
    user = models.OneToOneField(User, on_delete=models.CASCADE, related_name="subscription")
    plan = models.ForeignKey(SubscriptionPlan, on_delete=models.PROTECT)
    payment_gateway = models.CharField(
        max_length=20,
        choices=[
            ("paypal", "PayPal"),
            ("flutterwave", "Flutterwave"),
            ("monnify", "Monnify"),
        ],
        default="paypal"
    )
    gateway_reference = models.CharField(max_length=200, blank=True)
    status = models.CharField(
        max_length=20,
        choices=[
            ("active", "Active"),
            ("pending", "Pending"),
            ("cancelled", "Cancelled"),
        ],
        default="pending"
    )
    started_at = models.DateTimeField(auto_now_add=True)
    next_billing = models.DateTimeField()
    credits_remaining = models.PositiveIntegerField(default=0)


class CreditPurchase(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name="credit_purchases")
    amount = models.DecimalField(max_digits=10, decimal_places=2)
    gateway = models.CharField(max_length=20, choices=[
        ("paypal", "PayPal"),
        ("flutterwave", "Flutterwave"),
        ("monnify", "Monnify"),
    ])
    reference = models.CharField(max_length=200)
    status = models.CharField(max_length=20, default="pending")
    created_at = models.DateTimeField(auto_now_add=True)
