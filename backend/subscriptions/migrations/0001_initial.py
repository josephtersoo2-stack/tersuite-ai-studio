from django.conf import settings
from django.db import migrations, models
import django.db.models.deletion


class Migration(migrations.Migration):
    initial = True
    dependencies = [migrations.swappable_dependency(settings.AUTH_USER_MODEL)]
    operations = [
        migrations.CreateModel(
            name="SubscriptionPlan",
            fields=[
                ("id", models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name="ID")),
                ("name", models.CharField(max_length=100)),
                ("slug", models.CharField(max_length=50, unique=True)),
                ("price_monthly", models.DecimalField(decimal_places=2, max_digits=10)),
                ("generations_limit", models.PositiveIntegerField(default=10)),
                ("features", models.TextField(default="Standard pipeline, email support")),
                ("is_active", models.BooleanField(default=True)),
            ],
        ),
        migrations.CreateModel(
            name="CreditPurchase",
            fields=[
                ("id", models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name="ID")),
                ("amount", models.DecimalField(decimal_places=2, max_digits=10)),
                ("gateway", models.CharField(choices=[("paypal", "PayPal"), ("flutterwave", "Flutterwave"), ("monnify", "Monnify")], max_length=20)),
                ("reference", models.CharField(max_length=200)),
                ("status", models.CharField(default="pending", max_length=20)),
                ("created_at", models.DateTimeField(auto_now_add=True)),
                ("user", models.ForeignKey(on_delete=django.db.models.deletion.CASCADE, related_name="credit_purchases", to=settings.AUTH_USER_MODEL)),
            ],
        ),
        migrations.CreateModel(
            name="UserSubscription",
            fields=[
                ("id", models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name="ID")),
                ("payment_gateway", models.CharField(choices=[("paypal", "PayPal"), ("flutterwave", "Flutterwave"), ("monnify", "Monnify")], default="paypal", max_length=20)),
                ("gateway_reference", models.CharField(blank=True, max_length=200)),
                ("status", models.CharField(choices=[("active", "Active"), ("pending", "Pending"), ("cancelled", "Cancelled")], default="pending", max_length=20)),
                ("started_at", models.DateTimeField(auto_now_add=True)),
                ("next_billing", models.DateTimeField()),
                ("credits_remaining", models.PositiveIntegerField(default=0)),
                ("plan", models.ForeignKey(on_delete=django.db.models.deletion.PROTECT, to="subscriptions.subscriptionplan")),
                ("user", models.OneToOneField(on_delete=django.db.models.deletion.CASCADE, related_name="subscription", to=settings.AUTH_USER_MODEL)),
            ],
        ),
    ]
