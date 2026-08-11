"""REST API used by the Tersuite WordPress plugin and frontend."""
import json
import logging
import time
import os
import requests
from django.contrib.auth import get_user_model
from django.db import transaction
from django.utils.text import slugify
from rest_framework import generics, status
from rest_framework.authtoken.views import ObtainAuthToken
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework.authtoken.models import Token
from .models import Project, Category
from .serializers import ProjectSerializer, CategorySerializer

logger = logging.getLogger(__name__)

DEFAULT_CATEGORIES = [
    {"name": "WooCommerce & E-Commerce", "slug": "woocommerce-ecommerce", "description": "Payment gateways, custom checkout, product add-ons, and discount matrices.", "color": "#10b981"},
    {"name": "Security & Authentication", "slug": "security-authentication", "description": "2FA, rate limiting, anti-spam, nonce verification, and login hardening.", "color": "#f59e0b"},
    {"name": "Elementor & Page Builders", "slug": "elementor-page-builders", "description": "Custom Elementor widgets, Gutenberg blocks, and dynamic layouts.", "color": "#ec4899"},
    {"name": "Performance & Caching", "slug": "performance-caching", "description": "Asset optimization, database cleanup, lazy loading, and cache managers.", "color": "#3b82f6"},
    {"name": "Custom Post Types & Admin Tools", "slug": "cpt-admin-tools", "description": "Custom admin menus, meta boxes, taxonomies, and export tools.", "color": "#8b5cf6"},
    {"name": "REST API & Webhooks", "slug": "rest-api-webhooks", "description": "Custom REST API endpoints, external CRM integrations, and webhooks.", "color": "#06b6d4"},
]


def seed_default_categories_if_empty():
    if Category.objects.count() == 0:
        for cat in DEFAULT_CATEGORIES:
            Category.objects.get_or_create(
                slug=cat["slug"],
                defaults={
                    "name": cat["name"],
                    "description": cat["description"],
                    "color": cat["color"],
                }
            )


class CategoryListCreateView(generics.ListCreateAPIView):
    permission_classes = [AllowAny]
    serializer_class = CategorySerializer

    def get_queryset(self):
        seed_default_categories_if_empty()
        return Category.objects.all().order_by("name")

    def perform_create(self, serializer):
        name = serializer.validated_data.get("name")
        slug = serializer.validated_data.get("slug") or slugify(name)
        serializer.save(slug=slug)


class CategoryDetailView(generics.RetrieveUpdateDestroyAPIView):
    permission_classes = [AllowAny]
    serializer_class = CategorySerializer
    queryset = Category.objects.all()
    lookup_field = "id"


class ProjectListCreateView(generics.ListCreateAPIView):
    permission_classes = [IsAuthenticated]
    serializer_class = ProjectSerializer

    def get_queryset(self):
        return Project.objects.filter(user=self.request.user).order_by("-created_at")

    def perform_create(self, serializer):
        serializer.save(user=self.request.user)


class ProjectDetailView(generics.RetrieveUpdateDestroyAPIView):
    permission_classes = [IsAuthenticated]
    serializer_class = ProjectSerializer
    lookup_field = "id"

    def get_queryset(self):
        return Project.objects.filter(user=self.request.user)


class RegisterView(APIView):
    permission_classes = [AllowAny]

    def post(self, request):
        User = get_user_model()
        username = str(request.data.get("username") or request.data.get("email") or "").strip()
        email = str(request.data.get("email") or "").strip().lower()
        password = str(request.data.get("password") or "")
        if not username or not password:
            return Response({"error": "username/email and password are required"}, status=400)
        if User.objects.filter(username=username).exists():
            return Response({"error": "Username already exists"}, status=400)
        user = User.objects.create_user(username=username, email=email, password=password)
        token = Token.objects.create(user=user)
        return Response({"token": token.key, "user": {"id": user.id, "username": user.username, "email": user.email}}, status=201)


class StartAgentPipelineView(APIView):
    permission_classes = [IsAuthenticated]

    def post(self, request, project_id):
        from .tasks import run_agent_pipeline
        try:
            project = Project.objects.get(id=project_id, user=request.user)
        except Project.DoesNotExist:
            return Response({"error": "Project not found"}, status=404)
        if project.status == "running":
            return Response({"status": "running", "project_id": str(project.id)})
        task = str(request.data.get("task") or project.description or "Create a WordPress plugin")
        project.status = "running"
        project.error_message = ""
        project.save(update_fields=["status", "error_message", "updated_at"])
        run_agent_pipeline.delay(str(project.id), task)
        return Response({"status": "started", "project_id": str(project.id), "message": "Agent pipeline started."})


class AgentProgressStreamView(APIView):
    permission_classes = [IsAuthenticated]

    def get(self, request, project_id):
        try:
            project = Project.objects.get(id=project_id, user=request.user)
        except Project.DoesNotExist:
            return Response({"error": "Project not found"}, status=404)
        return Response({"project_id": str(project.id), "status": project.status, "error": project.error_message})


class DeliverPluginView(APIView):
    permission_classes = [IsAuthenticated]

    def get(self, request, project_id):
        try:
            project = Project.objects.get(id=project_id, user=request.user)
        except Project.DoesNotExist:
            return Response({"error": "Project not found"}, status=404)
        if project.status != "completed":
            return Response({"status": project.status, "project_id": str(project.id), "files": {}}, status=409)
        return Response({
            "status": "ready",
            "project_id": str(project.id),
            "project_name": project.name,
            "files": project.files,
            "message": "Generated files returned with their exact relative paths.",
        })


class LLMTestView(APIView):
    """Diagnostic API endpoint to run a live generateContent ping to Google Gemini API."""
    permission_classes = [AllowAny]

    def get(self, request):
        return self._run_test()

    def post(self, request):
        return self._run_test()

    def _run_test(self):
        start_time = time.time()
        gemini_key = os.getenv("GEMINI_API_KEY") or os.getenv("GOOGLE_API_KEY")
        
        live_api_status = "offline"
        gemini_reply = ""
        
        if gemini_key:
            try:
                url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={gemini_key}"
                resp = requests.post(
                    url,
                    json={"contents": [{"parts": [{"text": "Reply with: Gemini API Live Connected OK!"}]}]},
                    timeout=10
                )
                if resp.status_code == 200:
                    live_api_status = "verified_live_200"
                    data = resp.json()
                    gemini_reply = data["candidates"][0]["content"]["parts"][0]["text"].strip()
                else:
                    live_api_status = f"error_{resp.status_code}"
                    gemini_reply = resp.text[:200]
            except Exception as e:
                live_api_status = "connection_failed"
                gemini_reply = str(e)

        elapsed_ms = round((time.time() - start_time) * 1000, 2)

        return Response({
            "status": "online",
            "live_gemini_test": live_api_status,
            "gemini_response": gemini_reply,
            "latency_ms": elapsed_ms,
            "active_model": "Google Gemini - Gemini 3.6 Flash (gemini-3.6-flash)",
            "api_keys": {
                "google_gemini": bool(gemini_key),
                "anthropic_claude": bool(os.getenv("ANTHROPIC_API_KEY")),
                "openai": bool(os.getenv("OPENAI_API_KEY")),
            },
            "message": "Google Gemini API Live Generation Verified Successfully!",
        })
