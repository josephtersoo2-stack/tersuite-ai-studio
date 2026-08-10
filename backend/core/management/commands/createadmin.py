from django.core.management.base import BaseCommand
from django.contrib.auth import get_user_model
from rest_framework.authtoken.models import Token
import os


class Command(BaseCommand):
    help = "Create or update the Tersuite Django admin account from environment variables."

    def handle(self, *args, **options):
        User = get_user_model()
        username = os.getenv("DJANGO_ADMIN_USERNAME", "admin")
        email = os.getenv("DJANGO_ADMIN_EMAIL", "admin@example.com")
        password = os.getenv("DJANGO_ADMIN_PASSWORD")
        if not password:
            raise RuntimeError("DJANGO_ADMIN_PASSWORD must be set before creating the admin")
        user, _ = User.objects.get_or_create(username=username, defaults={"email": email})
        user.email = email
        user.is_staff = True
        user.is_superuser = True
        user.set_password(password)
        user.save()
        token, _ = Token.objects.get_or_create(user=user)
        self.stdout.write(self.style.SUCCESS(f"Admin ready: {username}; API token: {token.key}"))
