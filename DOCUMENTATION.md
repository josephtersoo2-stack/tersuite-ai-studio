# Tersuite AI Studio Architecture Documentation

## Overview
Tersuite AI Studio creates WordPress plugins using multi-agent AI, with a Django backend handling concurrent tasks and a separate WordPress plugin connecting users to the system.

## Components

### 1. Django Backend (`backend/`)
- **Framework**: Django + DRF + Django Channels (WebSocket) + Celery + Redis + PostgreSQL
- **Admin Dashboard**: Django Admin with configurable LLM registry (add/delete providers and models)
- **LLM Registry**: Dynamic database-driven providers (OpenAI, Gemini, Anthropic, GLM, Kimi, DeepSeek) - never hardcoded
- **Agent Framework** (`agents/`): Multi-agent pipeline
  - Coordinator (Planner)
  - Sub-agents: UI/UX, Frontend, Backend, Security, Coder, Review, Sandbox
- **Sandbox Manager**: MicroVM isolation (Firecracker/Kata) instead of standard Docker
- **Knowledge Base**: Pre-built WP plugin templates (security boilerplate, modern PHP practices)

### 2. WordPress Plugin (`wp-plugin/`)
- Connects to Django backend via REST API
- Project manager (create/open/delete)
- IDE page (chat with agents, file tree/file manager)
- Real-time agent progress streaming
- Zip/download plugin for installation
- Settings page (API keys, backend URL)

### 3. Separate Frontend (`frontend/`)
- User registration and login
- API key generation
- Subscription management (PayPal, Flutterwave, Monnify)
- Separate from Django admin dashboard

### 4. Sandbox (`sandbox/templates/`)
- Plugin boilerplate with modern WP security standards
- Theme boilerplate for future expansion
- Security templates (nonces, sanitization, capabilities, prepared statements)

## Security Features
- Zero-Trust: ABSPATH guards, nonces, capability checks
- Sanitization: `sanitize_text_field`, `sanitize_email`, `sanitize_key`
- Database: `$wpdb->prepare()` (prepared statements)
- Isolation: MicroVM sandbox for AI-generated code testing

## Deployment / Hosting Setup
- **Framework**: CrewAI (open-source, MIT) installed at hosting time (`pip install crewai>=0.85`)
- **Backend**: Django + Celery workers + Redis + PostgreSQL via Docker Compose (`docker-compose.yml`)
- **Sandbox**: Firecracker/Kata microVM isolation (not standard Docker) for untrusted AI-generated plugin code
- **LLM Registry**: Configured in Django Admin before deployment (add providers/models, never hardcoded)
- **WordPress Plugin**: Upload `.zip` from `wp-plugin/` to hosting WP site; configure API URL/settings
- **Frontend**: Deploy `frontend/` separately; integrate with backend API for registration/keys/subscriptions

## Multi-Agent Pipeline
1. User creates project in WP plugin
2. Coordinator agent plans with user (interactive planning)
3. Tasks distributed to sub-agents
4. Sub-agents work concurrently (Celery tasks)
5. Security agent applies WP security standards
6. Review agent checks code
7. Sandbox agent tests in isolated microVM
8. Results zipped and delivered to WP plugin
