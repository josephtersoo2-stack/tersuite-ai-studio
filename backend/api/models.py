import uuid
from django.conf import settings
from django.db import models


class Project(models.Model):
    STATUS_CHOICES = [
        ("draft", "Draft"),
        ("running", "Running"),
        ("testing", "Testing"),
        ("completed", "Completed"),
        ("failed", "Failed"),
    ]

    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    user = models.ForeignKey(settings.AUTH_USER_MODEL, on_delete=models.CASCADE, related_name="tersuite_projects")
    name = models.CharField(max_length=200)
    description = models.TextField(blank=True)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default="draft")
    workspace_path = models.CharField(max_length=1000, blank=True)
    files = models.JSONField(default=dict, blank=True)
    last_result = models.JSONField(default=dict, blank=True)
    error_message = models.TextField(blank=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    def __str__(self):
        return self.name
