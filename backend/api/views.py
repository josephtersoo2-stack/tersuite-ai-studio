"""REST API used by the Tersuite WordPress plugin and frontend."""
import json
import logging
from django.contrib.auth import get_user_model
from django.db import transaction
from rest_framework import generics, status
from rest_framework.authtoken.views import ObtainAuthToken
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework.authtoken.models import Token
from .models import Project
from .serializers import ProjectSerializer

logger = logging.getLogger(__name__)


class ProjectListCreateView(generics.ListCreateAPIView):
    permission_classes = [IsAuthenticated]
    serializer_class = ProjectSerializer

    def get_queryset(self):
        return Project.objects.filter(user=self.request.user).order_by("-created_at")

    def perform_create(self, serializer):
        serializer.save(user=self.request.user)


class ProjectDetailView(generics.RetrieveDestroyAPIView):
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
    """Return the generated project as a structured file map; WordPress creates the ZIP."""
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
            "message": "Generated files returned with their exact relative paths. The WordPress plugin can package them into a ZIP.",
        })
