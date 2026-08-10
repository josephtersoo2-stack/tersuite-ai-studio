from rest_framework import generics, status
from rest_framework.response import Response
from .models import SubscriptionPlan, UserSubscription

class PlanListView(generics.ListAPIView):
    queryset = SubscriptionPlan.objects.filter(is_active=True)
    serializer_class = None  # Would use DRF serializer in full implementation

    def list(self, request, *args, **kwargs):
        plans = list(self.get_queryset().values("name", "slug", "price_monthly", "features"))
        return Response(plans)

class SubscribeView(generics.GenericAPIView):
    def post(self, request, *args, **kwargs):
        gateway = request.data.get("gateway", "paypal")
        if gateway not in ["paypal", "flutterwave", "monnify"]:
            return Response({"error": "Invalid gateway"}, status=status.HTTP_400_BAD_REQUEST)
        # In production: redirect to gateway checkout URL
        return Response({
            "status": "redirect",
            "gateway": gateway,
            "message": f"Redirect to {gateway} checkout.",
            "checkout_url": f"/checkout/{gateway}/",
        })

class UserSubscriptionStatusView(generics.GenericAPIView):
    def get(self, request, *args, **kwargs):
        user = request.user
        try:
            sub = UserSubscription.objects.get(user=user)
            return Response({
                "plan": sub.plan.name,
                "status": sub.status,
                "credits_remaining": sub.credits_remaining,
                "next_billing": sub.next_billing,
                "payment_gateway": sub.payment_gateway,
            })
        except UserSubscription.DoesNotExist:
            return Response({
                "plan": None,
                "status": "none",
                "credits_remaining": 0,
                "message": "No active subscription.",
            })


class PurchaseCreditsView(generics.GenericAPIView):
    def post(self, request, *args, **kwargs):
        gateway = request.data.get("gateway", "paypal")
        amount = float(request.data.get("amount", 0))
        if gateway not in ["paypal", "flutterwave", "monnify"]:
            return Response({"error": "Invalid gateway"}, status=status.HTTP_400_BAD_REQUEST)
        return Response({
            "status": "pending",
            "gateway": gateway,
            "amount": amount,
            "message": f"Credit purchase of ${amount} via {gateway} initiated.",
        })
