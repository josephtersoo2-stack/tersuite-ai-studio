# Tersuite AI Studio — Railway deployment

## Services

Create one Railway project with these services:

1. `backend` — build from `backend/Dockerfile`, public HTTP service.
2. `worker` — same backend source/image, start command: `celery -A core worker --loglevel=info --concurrency=2`.
3. `frontend` — build from `frontend/Dockerfile`, public HTTP service.
4. PostgreSQL — Railway PostgreSQL service.
5. Redis — Railway Redis service.

## Backend variables

Required:

- `DJANGO_SECRET_KEY` — long random production secret.
- `DEBUG=False`
- `ALLOWED_HOSTS=<backend-domain>`
- `CSRF_TRUSTED_ORIGINS=https://<backend-domain>`
- `CORS_ALLOWED_ORIGINS=https://<frontend-domain>`
- `DJANGO_ADMIN_USERNAME`
- `DJANGO_ADMIN_EMAIL`
- `DJANGO_ADMIN_PASSWORD`
- `CREWAI_MODEL` (optional if the LLM registry supplies a model)
- Provider keys such as `OPENAI_API_KEY`, `GEMINI_API_KEY`, `ANTHROPIC_API_KEY`, `GLM_API_KEY`, `KIMI_API_KEY`, `DEEPSEEK_API_KEY` as configured in the LLM registry.

Railway PostgreSQL should provide `DATABASE_URL` and Redis should provide `REDIS_URL` to the backend and worker.

## CrewAI

`backend/requirements.txt` explicitly installs open-source CrewAI (`crewai>=0.85`) during the Railway image build. CrewAI is therefore installed on Railway, not stored in the source ZIP.

## Next.js

The frontend Dockerfile runs `npm install`, `npm run build`, and `npm start` during deployment. Set:

`NEXT_PUBLIC_API_URL=https://<backend-domain>/api`

## Generated plugin delivery

The backend stores the generated file map in the Project record. Each key is the exact relative path and each value is the UTF-8 file contents. The `/api/projects/<id>/deliver/` endpoint returns that map.

The WordPress plugin downloads that JSON, validates paths against traversal, and creates the final ZIP locally with PHP `ZipArchive`, preserving every directory and filename returned by the backend.

This avoids relying on ephemeral Railway filesystem storage for the final download.

## WebSockets

The backend exposes:

`/ws/progress/<project_id>/?token=<DRF_TOKEN>`

The WordPress plugin passes its configured API token through the WebSocket URL. This is required because a browser WebSocket cannot use the normal Authorization header used by REST requests.

## Deployment order

1. Deploy backend.
2. Add PostgreSQL and Redis and expose `DATABASE_URL`/`REDIS_URL` to backend and worker.
3. Run migrations automatically through the backend container.
4. `createadmin` creates the configured Django admin and prints/creates its DRF token.
5. Open `/admin/` and configure the LLM registry.
6. Deploy the worker using the same backend image with the Celery command.
7. Deploy Next.js with `NEXT_PUBLIC_API_URL` pointing to the backend.
8. Install the WordPress plugin ZIP on the WordPress site.
9. In Tersuite settings, set Backend API URL to the backend `/api` URL, API Key to a DRF token for the WordPress user, and Stream URL to the backend WebSocket origin (`wss://...`).
10. Create a plugin project, start generation, wait for `completed`, then download the ZIP.
