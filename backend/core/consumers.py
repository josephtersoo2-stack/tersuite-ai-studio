"""
WebSocket consumer for real-time agent progress streaming.
Connects WordPress plugin to Django backend for live updates.
"""
import json
import logging
from channels.generic.websocket import AsyncWebsocketConsumer
from channels.db import database_sync_to_async

logger = logging.getLogger(__name__)


class AgentProgressConsumer(AsyncWebsocketConsumer):
    """Streams multi-agent pipeline progress in real-time."""

    async def connect(self):
        self.project_id = self.scope["url_route"]["kwargs"]["project_id"]
        self.room_group_name = f"agent_progress_{self.project_id}"

        # Check authentication via scope (set by AuthMiddlewareStack)
        user = self.scope.get("user")
        if not user or user.is_anonymous:
            await self.close(code=4001)
            return

        await self.channel_layer.group_add(
            self.room_group_name,
            self.channel_name
        )
        await self.accept()
        await self.send(text_data=json.dumps({
            "type": "connection_established",
            "project_id": self.project_id,
            "message": "Real-time agent stream connected.",
        }))

    async def disconnect(self, close_code):
        await self.channel_layer.group_discard(
            self.room_group_name,
            self.channel_name
        )

    async def receive(self, text_data):
        data = json.loads(text_data)
        # Handle commands from WP plugin (e.g., stop agent, request status)
        if data.get("action") == "request_status":
            await self.send_agent_status()
        elif data.get("action") == "start_pipeline":
            await self.trigger_pipeline_start(data.get("task", "Generate plugin"))

    async def agent_progress(self, event):
        """Broadcast agent updates to connected clients."""
        await self.send(text_data=json.dumps({
            "type": "agent_update",
            "agent": event.get("agent"),
            "status": event.get("status"),
            "message": event.get("message"),
            "project_id": self.project_id,
            "timestamp": event.get("timestamp"),
        }))

    async def send_agent_status(self):
        await self.send(text_data=json.dumps({
            "type": "status",
            "project_id": self.project_id,
            "message": "Pipeline running. Monitoring agent progress...",
        }))

    async def trigger_pipeline_start(self, task_description: str):
        from api.tasks import run_agent_pipeline
        # Trigger Celery task asynchronously
        run_agent_pipeline.delay(self.project_id, task_description)
        await self.send(text_data=json.dumps({
            "type": "pipeline_started",
            "project_id": self.project_id,
            "task": task_description,
        }))
