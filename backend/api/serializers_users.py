"""
User Serializers for Module 02 Users.
Strictly masks passwords, password hashes, and private secrets.
Exposes read-only project and subscription relationship summaries.
"""
from django.contrib.auth import get_user_model
from rest_framework import serializers

User = get_user_model()


class UserSubscriptionSummarySerializer(serializers.Serializer):
    status = serializers.CharField(read_only=True)
    plan_name = serializers.CharField(read_only=True)
    credits_remaining = serializers.IntegerField(read_only=True)


class UserSerializer(serializers.ModelSerializer):
    projects_count = serializers.IntegerField(read_only=True, default=0)
    subscription = serializers.SerializerMethodField()

    class Meta:
        model = User
        fields = [
            "id",
            "username",
            "email",
            "first_name",
            "last_name",
            "is_active",
            "is_staff",
            "is_superuser",
            "date_joined",
            "last_login",
            "projects_count",
            "subscription",
        ]
        read_only_fields = fields

    def get_subscription(self, obj):
        try:
            sub = getattr(obj, "subscription", None)
            if sub:
                return {
                    "status": sub.status,
                    "plan_name": sub.plan.name if sub.plan else "Standard",
                    "credits_remaining": sub.credits_remaining,
                }
        except Exception:
            pass
        return {
            "status": "none",
            "plan_name": "Free Trial",
            "credits_remaining": 100,
        }


class UserProfileUpdateSerializer(serializers.ModelSerializer):
    class Meta:
        model = User
        fields = ["email", "first_name", "last_name"]

    def validate_email(self, value):
        user = self.context["request"].user
        email = value.strip().lower()
        if User.objects.filter(email=email).exclude(id=user.id).exists():
            raise serializers.ValidationError("This email address is already in use.")
        return email
