"""
URL Configuration for Module 02 Users API Endpoints.
"""
from django.urls import path
from . import views_users

urlpatterns = [
    path("", views_users.UserListAPIView.as_view(), name="user-list"),
    path("me/", views_users.UserProfileAPIView.as_view(), name="user-me"),
    path("<int:pk>/", views_users.UserDetailAPIView.as_view(), name="user-detail"),
    path("<int:pk>/activate/", views_users.UserActivateAPIView.as_view(), name="user-activate"),
    path("<int:pk>/deactivate/", views_users.UserDeactivateAPIView.as_view(), name="user-deactivate"),
]
