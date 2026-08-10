from django.db import models

class LLMProvider(models.Model):
    """Configurable LLM provider registry (add/delete in Django Admin)."""
    name = models.CharField(max_length=100, unique=True)
    display_name = models.CharField(max_length=200)
    api_base_url = models.URLField()
    api_key_env_var = models.CharField(
        max_length=200,
        help_text="Environment variable name that holds the API key (e.g., OPENAI_API_KEY)"
    )
    enabled = models.BooleanField(default=True)
    order = models.PositiveIntegerField(default=0)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        ordering = ["order", "name"]
        verbose_name = "LLM Provider"
        verbose_name_plural = "LLM Providers"

    def __str__(self):
        return f"{self.display_name} ({'enabled' if self.enabled else 'disabled'})"


class LLMModel(models.Model):
    """Specific model versions per provider (latest models only by default)."""
    provider = models.ForeignKey(LLMProvider, on_delete=models.CASCADE, related_name="models")
    model_id = models.CharField(max_length=200, help_text="Actual API model identifier (e.g., gpt-5.6-sol)")
    display_name = models.CharField(max_length=200)
    version_tag = models.CharField(max_length=50, default="latest", help_text="e.g., '2026-07', 'v1.0'")
    is_default = models.BooleanField(default=False)
    is_latest = models.BooleanField(default=True)
    capabilities = models.CharField(
        max_length=500,
        default="coding,reasoning,agentic",
        help_text="Comma-separated capabilities (coding,security,ui,frontend,backend,review)"
    )
    max_tokens = models.PositiveIntegerField(default=128000)
    cost_input_per_million = models.DecimalField(max_digits=12, decimal_places=4, default=0.0)
    cost_output_per_million = models.DecimalField(max_digits=12, decimal_places=4, default=0.0)
    enabled = models.BooleanField(default=True)

    class Meta:
        ordering = ["provider", "-is_latest", "-is_default"]
        unique_together = [["provider", "model_id"]]
        verbose_name = "LLM Model"
        verbose_name_plural = "LLM Models"

    def __str__(self):
        return f"{self.provider.name} - {self.display_name} ({self.model_id})"
