from datetime import timedelta
from django.test import TestCase
from django.contrib.auth import get_user_model
from django.utils import timezone
from rest_framework.test import APIClient
from rest_framework.authtoken.models import Token
from api.models import Project, Category
from subscriptions.models import UserSubscription, CreditPurchase, SubscriptionPlan
from llm_registry.models import LLMProvider, LLMModel

User = get_user_model()


class ComprehensiveDashboardTestCase(TestCase):
    def setUp(self):
        self.client = APIClient()
        self.staff_client = APIClient()
        self.user_b_client = APIClient()

        # User A (Normal)
        self.user_a = User.objects.create_user(
            username="user_a",
            email="usera@tersuite.com",
            password="password123"
        )
        self.token_a = Token.objects.create(user=self.user_a)
        self.client.credentials(HTTP_AUTHORIZATION="Token " + self.token_a.key)

        # User B (Normal)
        self.user_b = User.objects.create_user(
            username="user_b",
            email="userb@tersuite.com",
            password="password123"
        )
        self.token_b = Token.objects.create(user=self.user_b)
        self.user_b_client.credentials(HTTP_AUTHORIZATION="Token " + self.token_b.key)

        # Staff User
        self.staff_user = User.objects.create_user(
            username="staff_admin",
            email="staff@tersuite.com",
            password="password123",
            is_staff=True,
            is_superuser=True
        )
        self.token_staff = Token.objects.create(user=self.staff_user)
        self.staff_client.credentials(HTTP_AUTHORIZATION="Token " + self.token_staff.key)

        # Categories
        self.cat_ecommerce = Category.objects.create(name="E-Commerce", slug="e-commerce", color="#10b981")
        self.cat_security = Category.objects.create(name="Security", slug="security", color="#f59e0b")

        # Projects for User A (Exact choices: draft, running, testing, completed, failed)
        self.proj_a1 = Project.objects.create(user=self.user_a, category=self.cat_ecommerce, name="User A Draft", status="draft")
        self.proj_a2 = Project.objects.create(user=self.user_a, category=self.cat_ecommerce, name="User A Running", status="running")
        self.proj_a3 = Project.objects.create(user=self.user_a, category=self.cat_security, name="User A Testing", status="testing")
        self.proj_a4 = Project.objects.create(user=self.user_a, category=self.cat_security, name="User A Completed", status="completed")
        self.proj_a5 = Project.objects.create(user=self.user_a, category=self.cat_security, name="User A Failed", status="failed")

        # Project for User B
        self.proj_b1 = Project.objects.create(user=self.user_b, category=self.cat_ecommerce, name="User B Project", status="completed")

        # Subscription for User A
        self.plan = SubscriptionPlan.objects.create(name="Pro Plan", slug="pro-plan", price_monthly=29.99)
        self.sub_a = UserSubscription.objects.create(
            user=self.user_a,
            plan=self.plan,
            payment_gateway="paypal",
            status="active",
            next_billing=timezone.now() + timedelta(days=30),
            credits_remaining=250
        )

        # Credit Purchases
        self.purchase_a = CreditPurchase.objects.create(
            user=self.user_a,
            amount=50.00,
            gateway="paypal",
            reference="REF123",
            status="completed"
        )

        # LLM Provider Registry
        self.provider_openai = LLMProvider.objects.create(
            name="OpenAI",
            display_name="OpenAI GPT-4",
            api_base_url="https://api.openai.com/v1",
            api_key_env_var="OPENAI_API_KEY",
            enabled=True
        )

    # 1. Authentication Tests
    def test_unauthenticated_access_denied(self):
        unauth_client = APIClient()
        response = unauth_client.get("/api/v1/dashboard/overview/")
        self.assertEqual(response.status_code, 401)

    def test_authenticated_overview(self):
        response = self.client.get("/api/v1/dashboard/overview/")
        self.assertEqual(response.status_code, 200)
        self.assertIn("meta", response.data)
        self.assertIn("data", response.data)
        self.assertEqual(response.data["meta"]["period"], "30d")

    # 2. User Isolation Tests
    def test_user_project_isolation(self):
        response_a = self.client.get("/api/v1/dashboard/overview/")
        self.assertEqual(response_a.data["data"]["projects"]["total_projects"]["value"], 5)

        response_b = self.user_b_client.get("/api/v1/dashboard/overview/")
        self.assertEqual(response_b.data["data"]["projects"]["total_projects"]["value"], 1)

    # 3. Staff vs Non-Staff Revenue & User Stats Isolation
    def test_staff_vs_non_staff_isolation(self):
        # Non-staff user should NOT receive platform user metrics
        response_user = self.client.get("/api/v1/dashboard/overview/")
        self.assertIn("status", response_user.data["data"]["users"])
        self.assertEqual(response_user.data["data"]["users"]["status"], "restricted_to_staff")

        # Staff user should receive platform user metrics & revenue comparison
        response_staff = self.staff_client.get("/api/v1/dashboard/overview/")
        self.assertIn("total_users", response_staff.data["data"]["users"])
        self.assertTrue(response_staff.data["data"]["users"]["total_users"] >= 3)
        self.assertIn("revenue", response_staff.data["data"])

    # 4. Project Status Breakdown Tests
    def test_exact_project_status_counts(self):
        response = self.client.get("/api/v1/dashboard/overview/")
        proj_data = response.data["data"]["projects"]
        self.assertEqual(proj_data["draft_projects"], 1)
        self.assertEqual(proj_data["running_projects"], 1)
        self.assertEqual(proj_data["testing_projects"], 1)
        self.assertEqual(proj_data["completed_projects"], 1)
        self.assertEqual(proj_data["failed_projects"], 1)

    # 5. Period Parameter & Custom Range Validation Tests
    def test_valid_periods(self):
        for p in ["today", "7d", "30d", "90d", "ytd"]:
            response = self.client.get(f"/api/v1/dashboard/overview/?period={p}")
            self.assertEqual(response.status_code, 200)
            self.assertEqual(response.data["meta"]["period"], p)

    def test_invalid_period_returns_400(self):
        response = self.client.get("/api/v1/dashboard/overview/?period=banana")
        self.assertEqual(response.status_code, 400)
        self.assertIn("error", response.data)

    def test_custom_date_range_validation(self):
        now = timezone.now()
        start = (now - timedelta(days=10)).isoformat()
        end = now.isoformat()
        response = self.client.get(f"/api/v1/dashboard/overview/?period=custom&start_date={start}&end_date={end}")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data["meta"]["period"], "custom")

    # 6. Infrastructure Health Diagnostic Tests
    def test_health_endpoint(self):
        response = self.client.get("/api/v1/dashboard/health/")
        self.assertEqual(response.status_code, 200)
        services = response.data["data"]
        self.assertEqual(services["database"]["status"], "healthy")
        self.assertEqual(services["redis"]["status"], "healthy")
        self.assertTrue(len(services["llm_providers"]) >= 1)
        self.assertIn("channels", services)

    # 7. Activity Timeline Tests
    def test_activity_endpoint(self):
        response = self.client.get("/api/v1/dashboard/activity/")
        self.assertEqual(response.status_code, 200)
        self.assertIn("events", response.data["data"])
        self.assertTrue(len(response.data["data"]["events"]) >= 5)

    # 8. Unbuilt Future Module Declarations Test
    def test_future_modules_declarations(self):
        response = self.client.get("/api/v1/dashboard/overview/")
        future = response.data["data"]["future_modules"]
        self.assertIn("production_plans", future)
        self.assertEqual(future["production_plans"]["status"], "available_after_later_module")
