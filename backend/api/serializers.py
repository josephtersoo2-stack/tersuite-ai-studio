from rest_framework import serializers
from .models import Project


class ProjectSerializer(serializers.ModelSerializer):
    id = serializers.UUIDField(read_only=True)
    files = serializers.JSONField(read_only=True)
    status = serializers.CharField(read_only=True)
    error_message = serializers.CharField(read_only=True)

    class Meta:
        model = Project
        fields = ["id", "name", "description", "status", "files", "error_message", "created_at", "updated_at"]
        read_only_fields = ["id", "status", "files", "error_message", "created_at", "updated_at"]
