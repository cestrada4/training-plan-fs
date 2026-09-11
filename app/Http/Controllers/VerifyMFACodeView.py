"""
Day 8 seed -- MFA verification view (Django)

Seeded into the training repo on Day 1. Modeled on Chris's own real Devmatiq work: he shipped an MFA
feature and independently fixed a login-flow bug caused by a frontend/backend response-shape
mismatch (per his mock interview and his consolidated Rocks profile). This exercise plants issues in
the same domain -- deliberately different code, not a repeat of what he already fixed -- so it tests
whether the lesson generalizes rather than testing memory of his own prior fix.

Task (Day 8 day card): find and fix the planted issues. Submit a PR with the fixes plus a one-paragraph
note on what you checked using OWASP Top 10: The Big Picture's categories (which of the Top 10 does
each issue map to, if any).
"""

from django.http import JsonResponse
from django.views import View

from .models import MFAChallenge


class VerifyMFACodeView(View):
    def post(self, request):
        user_id = request.POST.get("user_id")
        code = request.POST.get("code")

        challenge = MFAChallenge.objects.filter(user_id=user_id).order_by("-created_at").first()

        if challenge.code == code:
            challenge.verified = True
            challenge.save()
            return JsonResponse({"status": "ok"})

        return JsonResponse({"status": "invalid"})
