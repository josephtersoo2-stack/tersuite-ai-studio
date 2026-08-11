"""
Django REST Framework Views for Module 02 Users.
Implements User list (paginated, search, filter), User detail, Profile update, Activation, and Deactivation.
"""
from django.contrib.auth import get_user_model
from django.core.exceptions import PermissionDenied
from django.db.models import Count
from rest_framework import generics, status
from rest_framework.permissions import IsAuthenticated, IsAdminUser
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework.pagination import PageNumberPagination

from .serializers_users import UserSerializer, UserProfileUpdateSerializer
from .services.users import build_user_queryset, activate_user, deactivate_user, UserActionException

User = get_user_model()


class UserPagination(PageNumberPagination):
    page_size = 20
    page_size_query_param = "page_size"
    max_page_size = 100


class UserListAPIView(generics.ListAPIView):
    """
    Paginated User List Endpoint for Staff/Admin.
    Supports search, filtering by status/date, and safe ordering whitelist.
    """
    permission_classes = [IsAuthenticated, IsAdminUser]
    serializer_class = UserSerializer
    pagination_class = UserPagination

    def get_queryset(self):
        return build_user_queryset(self.request.query_params)


class UserProfileAPIView(APIView):
    """
    Current User Profile Endpoint.
    GET: Returns current user's profile details.
    PATCH: Updates email, first_name, last_name.
    """
    permission_classes = [IsAuthenticated]

    def get(self, request):
        user = User.objects.annotate(
            projects_count=Count("tersuite_projects")
        ).select_related("subscription__plan").get(id=request.user.id)
        
        serializer = UserSerializer(user)
        return Response(serializer.data)

    def patch(self, request):
        user = request.user
        serializer = UserProfileUpdateSerializer(user, data=request.data, partial=True, context={"request": request})
        if serializer.is_valid():
            serializer.save()
            updated_user = User.objects.annotate(
                projects_count=Count("tersuite_projects")
            ).select_related("subscription__plan").get(id=user.id)
            return Response(UserSerializer(updated_user).data)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


class UserDetailAPIView(APIView):
    """
    User Detail Endpoint.
    Staff can retrieve details for any user ID; normal users can only retrieve their own ID.
    """
    permission_classes = [IsAuthenticated]

    def get(self, request, pk):
        is_staff = request.user.is_staff or request.user.is_superuser
        if not is_staff and str(request.user.id) != str(pk):
            return Response({"detail": "You do not have permission to access another user's profile."}, status=status.HTTP_403_FORBIDDEN)

        try:
            target_user = User.objects.annotate(
                projects_count=Count("tersuite_projects")
            ).select_related("subscription__plan").get(pk=pk)
        except User.DoesNotExist:
            return Response({"detail": "User not found."}, status=status.HTTP_404_NOT_FOUND)

        serializer = UserSerializer(target_user)
        return Response(serializer.data)


class UserActivateAPIView(APIView):
    """
    Activates a user account (is_active=True). Staff/Admin permission required.
    """
    permission_classes = [IsAuthenticated, IsAdminUser]

    def post(self, request, pk):
        try:
            target_user = User.objects.get(pk=pk)
        except User.DoesNotExist:
            return Response({"detail": "User not found."}, status=status.HTTP_404_NOT_FOUND)

        try:
            activate_user(target_user, request.user)
            return Response({
                "status": "success",
                "message": f"User '{target_user.username}' activated successfully.",
                "user_id": target_user.id,
                "is_active": True,
            })
        except PermissionDenied as e:
            return Response({"detail": str(e)}, status=status.HTTP_403_FORBIDDEN)


class UserDeactivateAPIView(APIView):
    """
    Deactivates a user account (is_active=False). Staff/Admin permission required.
    Includes self-deactivation safeguard for superusers.
    """
    permission_classes = [IsAuthenticated, IsAdminUser]

    def post(self, request, pk):
        try:
            target_user = User.objects.get(pk=pk)
        except User.DoesNotExist:
            return Response({"detail": "User not found."}, status=status.HTTP_404_NOT_FOUND)

        try:
            deactivate_user(target_user, request.user)
            return Response({
                "status": "success",
                "message": f"User '{target_user.username}' deactivated successfully.",
                "user_id": target_user.id,
                "is_active": False,
            })
        except UserActionException as e:
            return Response({"detail": str(e)}, status=status.HTTP_400_BAD_REQUEST)
        except PermissionDenied as e:
            return Response({"detail": str(e)}, status=status.HTTP_403_FORBIDDEN)
