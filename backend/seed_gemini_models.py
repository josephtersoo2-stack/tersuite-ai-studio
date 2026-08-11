import os
import sys
from pathlib import Path

# Add backend directory to sys.path
backend_dir = Path(__file__).resolve().parent
sys.path.insert(0, str(backend_dir))

os.environ.setdefault("DJANGO_SETTINGS_MODULE", "core.settings")

import django
django.setup()

from llm_registry.models import LLMProvider, LLMModel

provider, _ = LLMProvider.objects.get_or_create(
    name="gemini",
    defaults={
        "display_name": "Google Gemini",
        "api_base_url": "https://generativelanguage.googleapis.com",
        "api_key_env_var": "GEMINI_API_KEY",
        "enabled": True,
        "order": 1,
    }
)
provider.enabled = True
provider.save()

models_to_add = [
    # Gemini 3 & 3.5 Models
    {
        "model_id": "gemini-3.6-flash",
        "display_name": "Gemini 3.6 Flash",
        "version_tag": "3.6",
        "is_default": True,
        "is_latest": True,
        "capabilities": "coding,fast,agentic,reasoning,multimodal",
        "max_tokens": 1000000,
    },
    {
        "model_id": "gemini-3.5-flash",
        "display_name": "Gemini 3.5 Flash",
        "version_tag": "3.5",
        "is_default": False,
        "is_latest": True,
        "capabilities": "coding,fast,agentic,data_processing",
        "max_tokens": 1000000,
    },
    {
        "model_id": "gemini-3.5-flash-lite",
        "display_name": "Gemini 3.5 Flash-Lite",
        "version_tag": "3.5-lite",
        "is_default": False,
        "is_latest": True,
        "capabilities": "coding,fast,lite,high_frequency",
        "max_tokens": 1000000,
    },
    {
        "model_id": "gemini-3.1-pro-preview",
        "display_name": "Gemini 3.1 Pro Preview",
        "version_tag": "3.1-preview",
        "is_default": False,
        "is_latest": True,
        "capabilities": "coding,reasoning,agentic,deep_logic,security,ui",
        "max_tokens": 1000000,
    },
    # Gemini 2.5 Models
    {
        "model_id": "gemini-2.5-pro",
        "display_name": "Gemini 2.5 Pro",
        "version_tag": "2.5",
        "is_default": False,
        "is_latest": False,
        "capabilities": "coding,reasoning,agentic,analytics",
        "max_tokens": 1000000,
    },
    {
        "model_id": "gemini-2.5-flash",
        "display_name": "Gemini 2.5 Flash",
        "version_tag": "2.5",
        "is_default": False,
        "is_latest": False,
        "capabilities": "coding,fast,agentic,parsing",
        "max_tokens": 1000000,
    },
    {
        "model_id": "gemini-2.5-flash-lite",
        "display_name": "Gemini 2.5 Flash-Lite",
        "version_tag": "2.5-lite",
        "is_default": False,
        "is_latest": False,
        "capabilities": "fast,lite,lightweight",
        "max_tokens": 1000000,
    },
]

added_count = 0
for m in models_to_add:
    obj, created = LLMModel.objects.update_or_create(
        provider=provider,
        model_id=m["model_id"],
        defaults={
            "display_name": m["display_name"],
            "version_tag": m["version_tag"],
            "is_default": m["is_default"],
            "is_latest": m["is_latest"],
            "capabilities": m["capabilities"],
            "max_tokens": m["max_tokens"],
            "enabled": True,
        }
    )
    added_count += 1
    print(f"[{'ADDED' if created else 'UPDATED'}] {m['display_name']} ({m['model_id']})")

print(f"\nSuccessfully registered {added_count} Gemini models in database!")
