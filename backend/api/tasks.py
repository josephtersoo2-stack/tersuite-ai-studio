"""Celery tasks for the production multi-agent generation pipeline."""
import json
import logging
import os
import shutil
import tempfile
from pathlib import Path
from celery import shared_task
from asgiref.sync import async_to_sync
from channels.layers import get_channel_layer
from django.db import transaction
from .models import Project

logger = logging.getLogger(__name__)


def _broadcast(project_id, status, message, agent="system"):
    try:
        channel_layer = get_channel_layer()
        async_to_sync(channel_layer.group_send)(
            f"agent_progress_{project_id}",
            {"type": "agent_progress", "agent": agent, "status": status, "message": message},
        )
    except Exception:
        logger.exception("Unable to broadcast progress")


def _collect_files(workspace: str):
    root = Path(workspace).resolve()
    files = {}
    for path in root.rglob("*"):
        if not path.is_file():
            continue
        relative = path.relative_to(root).as_posix()
        if relative.startswith(".") or "__pycache__/" in relative:
            continue
        # Prevent unexpectedly large/binary files from being returned through JSON.
        size = path.stat().st_size
        if size > 2 * 1024 * 1024:
            raise ValueError(f"Generated file is too large for API delivery: {relative}")
        data = path.read_bytes()
        try:
            files[relative] = data.decode("utf-8")
        except UnicodeDecodeError:
            raise ValueError(f"Generated file is not UTF-8 text: {relative}")
    return files


@shared_task(bind=True, max_retries=2)
def run_agent_pipeline(self, project_id: str, task_description: str):
    """Generate files with CrewAI, validate them, run sandbox checks, then persist the exact file map."""
    try:
        project = Project.objects.get(id=project_id)
        workspace = tempfile.mkdtemp(prefix=f"tersuite_{project_id}_")
        project.workspace_path = workspace
        project.status = "running"
        project.save(update_fields=["workspace_path", "status", "updated_at"])
        _broadcast(project_id, "running", "Starting CrewAI multi-agent pipeline", "coordinator")

        from agents.framework import TersuiteAIStudioCoordinator
        coordinator = TersuiteAIStudioCoordinator(workspace_path=workspace)
        result = coordinator.run_pipeline(
            {"project_id": project_id, "task": task_description, "workspace_path": workspace},
            task_description,
        )
        _broadcast(project_id, "generated", "Agents completed code generation", "coder")

        files = _collect_files(workspace)
        if not files:
            raise RuntimeError("CrewAI completed without producing any files")

        # Basic safety checks before the sandbox phase.
        for relative in files:
            if relative.startswith("/") or ".." in Path(relative).parts:
                raise ValueError(f"Unsafe generated path: {relative}")

        project.status = "testing"
        project.save(update_fields=["status", "updated_at"])
        _broadcast(project_id, "testing", "Running generated plugin sandbox checks", "sandbox")

        from sandbox_manager.manager import SandboxManager
        sandbox = SandboxManager()
        sandbox_result = sandbox.deploy_to_sandbox(workspace)
        tests = sandbox.run_tests(workspace)
        if tests.get("tests_passed") is False:
            raise RuntimeError("Sandbox tests failed")

        project.files = files
        project.last_result = {
            "pipeline": result,
            "sandbox": {**sandbox_result, **tests},
            "file_count": len(files),
        }
        project.status = "completed"
        project.error_message = ""
        project.save(update_fields=["files", "last_result", "status", "error_message", "updated_at"])
        _broadcast(project_id, "completed", f"Generation complete: {len(files)} files ready for WordPress ZIP packaging", "review")
        return {"status": "completed", "project_id": project_id, "file_count": len(files)}
    except Exception as exc:
        logger.exception("Agent pipeline failed for %s", project_id)
        try:
            project = Project.objects.get(id=project_id)
            project.status = "failed"
            project.error_message = str(exc)[:4000]
            project.save(update_fields=["status", "error_message", "updated_at"])
        except Exception:
            pass
        _broadcast(project_id, "failed", str(exc), "system")
        raise self.retry(exc=exc, countdown=30)


@shared_task
def test_plugin_in_sandbox(project_id: str, workspace_path: str):
    from sandbox_manager.manager import SandboxManager
    manager = SandboxManager()
    result = manager.deploy_to_sandbox(workspace_path)
    tests = manager.run_tests(workspace_path)
    return {**result, **tests}
