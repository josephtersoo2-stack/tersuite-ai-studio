from datetime import timedelta
from django.test import TestCase
from django.contrib.auth import get_user_model
from django.utils import timezone
from rest_framework.test import APIClient
from rest_framework.authtoken.models import Token
from api.models import Project
from subscriptions.models import UserSubscription, SubscriptionPlan

User = get_user_model()


class Module02UsersTestCase(TestCase):
    def setUp(self):
        self.client = APIClient()
        self.staff_client = APIClient()

        # Normal User A
        self.user_a = User.objects.create_user(
            username="usera",
            email="usera@tersuite.com",
            password="password123",
            first_name="Alice",
            last_name="User"
        )
        self.token_a = Token.objects.create(user=self.user_a)
        self.client.credentials(HTTP_AUTHORIZATION="Token " + self.token_a.key)

        # Normal User B
        self.user_b = User.objects.create_user(
            username="userb",
            email="userb@tersuite.com",
            password="password123",
            first_name="Bob",
            last_name="User"
        )
        self.token_b = Token.objects.create(user=self.user_b)

        # Staff Superuser
        self.staff_user = User.objects.create_user(
            username="admin_user",
            email="admin@tersuite.com",
            password="password123",
            is_staff=True,
            is_superuser=True
        )
        self.token_staff = Token.objects.create(user=self.staff_user)
        self.staff_client.credentials(HTTP_AUTHORIZATION="Token " + self.token_staff.key)

        # Projects for User A
        Project.objects.create(user=self.user_a, name="Plugin A1", status="completed")
        Project.objects.create(user=self.user_a, name="Plugin A2", status="draft")

        # Subscription for User A
        self.plan = SubscriptionPlan.objects.create(name="Pro", slug="pro", price_monthly=19.99)
        UserSubscription.objects.create(
            user=self.user_a,
            plan=self.plan,
            status="active",
            next_billing=timezone.now() + timedelta(days=30),
            credits_remaining=150
        )

    # 1. Authentication
    def test_unauthenticated_user_list_denied(self):
        unauth = APIClient()
        response = unauth.get("/api/v1/users/")
        self.assertEqual(response.status_code, 401)

    # 2. Authorization & Isolation
    def test_normal_user_cannot_list_users(self):
        response = self.client.get("/api/v1/users/")
        self.assertEqual(response.status_code, 403)

    def test_staff_can_list_users(self):
        response = self.staff_client.get("/api/v1/users/")
        self.assertEqual(response.status_code, 200)
        self.assertIn("results", response.data)
        self.assertEqual(response.data["count"], 3)

    def test_normal_user_cannot_access_other_user_detail(self):
        response = self.client.get(f"/api/v1/users/{self.user_b.id}/")
        self.assertEqual(response.status_code, 403)

    def test_normal_user_can_access_own_detail(self):
        response = self.client.get(f"/api/v1/users/{self.user_a.id}/")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data["username"], "usera")
        self.assertEqual(response.data["projects_count"], 2)

    # 3. User Search, Filters, Safe Ordering & Pagination
    def test_user_search(self):
        response = self.staff_client.get("/api/v1/users/?search=usera")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data["count"], 1)
        self.assertEqual(response.data["results"][0]["username"], "usera")

    def test_user_filter_is_staff(self):
        response = self.staff_client.get("/api/v1/users/?is_staff=true")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data["count"], 1)
        self.assertEqual(response.data["results"][0]["username"], "admin_user")

    def test_user_safe_ordering(self):
        response = self.staff_client.get("/api/v1/users/?ordering=-projects_count")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data["results"][0]["username"], "usera")

    def test_invalid_ordering_fallback(self):
        response = self.staff_client.get("/api/v1/users/?ordering=malicious_field;DROP_TABLE")
        self.assertEqual(response.status_code, 200)
        self.assertIn("results", response.data)

    # 4. User Profile & Patch Update
    def test_user_profile_me(self):
        response = self.client.get("/api/v1/users/me/")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data["username"], "usera")

    def test_user_profile_patch_update(self):
        response = self.client.patch("/api/v1/users/me/", {"first_name": "AliceUpdated"}, format="json")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data["first_name"], "AliceUpdated")

    def test_privilege_escalation_prevented(self):
        response = self.client.patch("/api/v1/users/me/", {"is_staff": True, "is_superuser": True}, format="json")
        self.assertEqual(response.status_code, 200)
        self.user_a.refresh_from_db()
        self.assertFalse(self.user_a.is_staff)
        self.assertFalse(self.user_a.is_superuser)

    # 5. User Activation & Deactivation
    def test_staff_activate_deactivate_user(self):
        # Deactivate
        r_deact = self.staff_client.post(f"/api/v1/users/{self.user_b.id}/deactivate/")
        self.assertEqual(r_deact.status_code, 200)
        self.user_b.refresh_from_db()
        self.assertFalse(self.user_b.is_active)

        # Activate
        r_act = self.staff_client.post(f"/api/v1/users/{self.user_b.id}/activate/")
        self.assertEqual(r_act.status_code, 200)
        self.user_b.refresh_from_db()
        self.assertTrue(self.user_b.is_active)

    def test_superuser_self_deactivation_safeguard(self):
        response = self.staff_client.post(f"/api/v1/users/{self.staff_user.id}/deactivate/")
        self.assertEqual(response.status_code, 400)
        self.assertIn("Safeguard Alert", response.data["detail"])

    # 6. Sensitive Field Protection
    def test_sensitive_fields_masked(self):
        response = self.staff_client.get(f"/api/v1/users/{self.user_a.id}/")
        self.assertNotIn("password", response.data)
        self.assertNotIn("password_hash", response.data)
        self.assertNotIn("auth_token", response.data)
