from django.urls import path
from rest_framework.authtoken.views import obtain_auth_token
from . import views

urlpatterns = [
    path("categories/", views.CategoryListCreateView.as_view(), name="categories"),
    path("categories/<uuid:id>/", views.CategoryDetailView.as_view(), name="category-detail"),
    path("projects/", views.ProjectListCreateView.as_view(), name="projects"),
    path("projects/<uuid:id>/", views.ProjectDetailView.as_view(), name="project-detail"),
    path("projects/<uuid:project_id>/start/", views.StartAgentPipelineView.as_view(), name="start-pipeline"),
    path("projects/<uuid:project_id>/stream/", views.AgentProgressStreamView.as_view(), name="agent-stream"),
    path("projects/<uuid:project_id>/deliver/", views.DeliverPluginView.as_view(), name="deliver-plugin"),
    path("auth/login/", obtain_auth_token, name="api-token-auth"),
    path("auth/register/", views.RegisterView.as_view(), name="register"),
]
