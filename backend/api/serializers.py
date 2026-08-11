from rest_framework import serializers
from .models import Project, Category


class CategorySerializer(serializers.ModelSerializer):
    id = serializers.UUIDField(read_only=True)

    class Meta:
        model = Category
        fields = ["id", "name", "slug", "description", "color", "created_at"]
        read_only_fields = ["id", "created_at"]


class ProjectSerializer(serializers.ModelSerializer):
    id = serializers.UUIDField(read_only=True)
    category_id = serializers.UUIDField(write_only=True, required=False, allow_null=True)
    category = CategorySerializer(read_only=True)
    files = serializers.JSONField(read_only=True)
    status = serializers.CharField(read_only=True)
    error_message = serializers.CharField(read_only=True)

    class Meta:
        model = Project
        fields = ["id", "name", "description", "category", "category_id", "status", "files", "error_message", "created_at", "updated_at"]
        read_only_fields = ["id", "status", "files", "error_message", "created_at", "updated_at"]

    def create(self, validated_data):
        category_id = validated_data.pop("category_id", None)
        if category_id:
            try:
                validated_data["category"] = Category.objects.get(id=category_id)
            except Category.DoesNotExist:
                pass
        return super().create(validated_data)

    def update(self, instance, validated_data):
        category_id = validated_data.pop("category_id", None)
        if category_id is not None:
            if category_id:
                try:
                    instance.category = Category.objects.get(id=category_id)
                except Category.DoesNotExist:
                    pass
            else:
                instance.category = None
        return super().update(instance, validated_data)
