from urllib.parse import parse_qs
from channels.db import database_sync_to_async
from django.contrib.auth.models import AnonymousUser
from rest_framework.authtoken.models import Token


@database_sync_to_async
def get_user(token_key):
    try:
        return Token.objects.select_related("user").get(key=token_key).user
    except Token.DoesNotExist:
        return AnonymousUser()


class TokenQueryAuthMiddleware:
    """Authenticate cross-origin WebSocket clients using a DRF token query parameter."""
    def __init__(self, app):
        self.app = app

    async def __call__(self, scope, receive, send):
        query = parse_qs(scope.get("query_string", b"").decode("utf-8"))
        token = (query.get("token") or [""])[0]
        scope["user"] = await get_user(token) if token else AnonymousUser()
        return await self.app(scope, receive, send)
