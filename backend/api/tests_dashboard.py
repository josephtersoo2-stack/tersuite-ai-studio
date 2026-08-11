from django.test import TestCase
from django.contrib.auth import get_user_model
from rest_framework.test import APIClient
from rest_framework.authtoken.models import Token
from api.models import Project

User = get_user_model()


class DashboardAPITestCase(TestCase):
    def setUp(self):
        self.client = APIClient()
        self.user = User.objects.create_user(
            username="testuser",
            email="test@tersuite.com",
            password="securepassword123"
        )
        self.token = Token.objects.create(user=self.user)
        self.client.credentials(HTTP_AUTHORIZATION="Token " + self.token.key)

        # Create sample project
        self.project = Project.objects.create(
            user=self.user,
            name="Test Plugin",
            description="A test WordPress plugin",
            status="draft"
        )

    def test_dashboard_overview_authenticated(self):
        response = self.client.get("/api/v1/dashboard/overview/")
        self.assertEqual(response.status_code, 200)
        self.assertIn("projects", response.data)
        self.assertEqual(response.data["projects"]["total"], 1)
        self.assertEqual(response.data["projects"]["draft"], 1)

    def test_dashboard_health(self):
        response = self.client.get("/api/v1/dashboard/health/")
        self.assertEqual(response.status_code, 200)
        self.assertIn("overall_status", response.data)
        self.assertIn("services", response.data)
        self.assertEqual(response.data["services"]["database"]["status"], "HEALTHY")

    def test_dashboard_activity(self):
        response = self.client.get("/api/v1/dashboard/activity/")
        self.assertEqual(response.status_code, 200)
        self.assertIn("activity", response.data)
        self.assertTrue(len(response.data["activity"]) >= 1)

    def test_dashboard_unauthenticated_access_denied(self):
        unauth_client = APIClient()
        response = unauth_client.get("/api/v1/dashboard/overview/")
        self.assertEqual(response.status_code, 401)
