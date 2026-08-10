from django.contrib import admin
from .models import LLMProvider, LLMModel


class LLMModelInline(admin.TabularInline):
    model = LLMModel
    extra = 1
    fields = [
        "model_id", "display_name", "version_tag", "is_default", "is_latest",
        "capabilities", "enabled"
    ]


@admin.register(LLMProvider)
class LLMProviderAdmin(admin.ModelAdmin):
    list_display = ["name", "display_name", "enabled", "order", "updated_at"]
    list_filter = ["enabled"]
    search_fields = ["name", "display_name"]
    ordering = ["order", "name"]
    inlines = [LLMModelInline]
    fieldsets = (
        (None, {
            "fields": ("name", "display_name", "enabled", "order")
        }),
        ("API Configuration", {
            "fields": ("api_base_url", "api_key_env_var"),
            "classes": ("collapse",),
        }),
    )


@admin.register(LLMModel)
class LLMModelAdmin(admin.ModelAdmin):
    list_display = ["provider", "display_name", "model_id", "is_latest", "is_default", "enabled"]
    list_filter = ["provider", "is_latest", "is_default", "enabled"]
    search_fields = ["model_id", "display_name"]
    actions = ["set_as_latest", "disable_models"]

    def set_as_latest(self, request, queryset):
        queryset.update(is_latest=True)
    set_as_latest.short_description = "Mark selected as latest versions"

    def disable_models(self, request, queryset):
        queryset.update(enabled=False)
    disable_models.short_description = "Disable selected models"
