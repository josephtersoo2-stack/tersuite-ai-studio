# Antigravity implementation contract

Read `docs/ANTIGRAVITY-IMPLEMENTATION-PLAN.md` first. It is the canonical implementation plan for the Coordinator-first, parallel-worker architecture.

Non-negotiable:
- one user-facing Coordinator;
- no agent selector in chat;
- user + Coordinator plan before production;
- explicit production approval;
- Django/Celery handles parallel specialist execution;
- WebSocket streams worker status;
- every completed session produces a persisted comprehensive summary;
- Studio loads project context immediately;
- Coordinator can explain Tersuite navigation and direct the user to the correct menu;
- preserve exact generated file structure;
- no provider secrets in WordPress.
