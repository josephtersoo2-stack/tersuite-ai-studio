from django.contrib import admin
from django.urls import include, path
from django.http import JsonResponse


def health(_request):
    return JsonResponse({"status": "ok", "service": "tersuite-backend"})


urlpatterns = [
    path("health/", health),
    path("admin/", admin.site.urls),
    path("api/", include("api.urls")),
    path("api/subscriptions/", include("subscriptions.urls")),
]
