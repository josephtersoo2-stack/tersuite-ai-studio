"""
User Lifecycle Actions & Security Safeguards.
Handles activation, deactivation, and self-lockout prevention.
"""
from django.core.exceptions import PermissionDenied


class UserActionException(ValueError):
    pass


def activate_user(target_user, requesting_user):
    """
    Activates a user account (is_active=True). Staff permission required.
    """
    if not requesting_user.is_staff and not requesting_user.is_superuser:
        raise PermissionDenied("Administrative permission required to activate accounts.")

    target_user.is_active = True
    target_user.save(update_fields=["is_active"])
    return target_user


def deactivate_user(target_user, requesting_user):
    """
    Deactivates a user account (is_active=False). Staff permission required.
    Safeguard: Superuser cannot deactivate their own active administrative account.
    """
    if not requesting_user.is_staff and not requesting_user.is_superuser:
        raise PermissionDenied("Administrative permission required to deactivate accounts.")

    if target_user.id == requesting_user.id and requesting_user.is_superuser:
        raise UserActionException("Safeguard Alert: Administrative superuser cannot deactivate their own account.")

    target_user.is_active = False
    target_user.save(update_fields=["is_active"])
    return target_user
