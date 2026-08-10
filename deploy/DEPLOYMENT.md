# Tersuite AI Studio Deployment Manifest

## Components for Hosting Installation
- `plugin/` - WordPress plugin (.zip package ready for WP admin upload)
- `backend/` - Django application (deploy to server with gunicorn + nginx)
- `frontend/` - User-facing web app (optional, deploy separately)
- `docker-compose.yml` - Production container setup

## Installation Steps (Direct to Hosting)
1. Upload `plugin/` contents to `/wp-content/plugins/tersuite/` on WordPress site
2. Configure settings (API URL, API Key, Stream URL) in WP admin
3. Deploy `backend/` on hosting server (install `crewai>=0.85` from requirements.txt during deployment)
4. Set environment variables for LLM providers (OpenAI, Gemini, Anthropic, GLM, Kimi, DeepSeek keys)
5. Configure Django Admin (add LLM providers/models via admin interface)
6. Start Celery workers, Channels, and WebSocket server
7. Deploy `frontend/` separately for user registration/subscription
8. Configure sandbox isolation (Firecracker/Kata) on hosting server

## Key Notes
- CrewAI framework installed at deployment time (not in workspace)
- MicroVM sandbox replaces standard Docker for security
- All security templates use Zero-Trust practices (ABSPATH, nonces, capabilities, sanitize/escape, $wpdb->prepare())
