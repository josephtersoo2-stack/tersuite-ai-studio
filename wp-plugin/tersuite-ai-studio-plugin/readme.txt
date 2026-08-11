=== Tersuite AI Studio ===
Contributors: tersuite
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later

Tersuite AI Studio is the customer-facing WordPress client for the Tersuite AI backend.

The AI Studio uses one user-facing Tersuite Coordinator. Specialist agents are internal workers orchestrated by the Django/Celery backend. Users plan with the Coordinator, explicitly approve production, watch parallel worker progress, and receive a comprehensive session completion report.

Installation:
1. Upload the ZIP through Plugins -> Add New -> Upload Plugin.
2. Activate the plugin.
3. Open Tersuite AI Studio -> Settings.
4. Enter the Django backend URL, WebSocket URL and user-scoped Tersuite credential.
5. Test the connection.

Important:
- CrewAI, Celery, Redis and PostgreSQL belong on the backend/Railway side.
- The plugin does not execute CrewAI locally.
- The plugin does not expose LLM provider API keys.
- Generated file paths must be preserved exactly when packaging deliveries.

Implementation guide:
See docs/ANTIGRAVITY-IMPLEMENTATION-PLAN.md.
