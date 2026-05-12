# Backend KYC Prembly + Email Verification Note

Date: 2026-05-12

## Summary

NaijaBuilders now has the backend KYC provider layer prepared for Prembly-backed supplier onboarding and reusable buyer progressive KYC. The Prembly API key is read only from Laravel environment/config as `PREMBLY_API_KEY`; no key is committed, logged, exposed to React/Expo, or returned by an API response.

## Current Laravel API Endpoints

- `POST /api/mobile/supplier/onboarding/business-details`
- `POST /api/mobile/supplier/onboarding/identity-verification`
- `POST /api/mobile/supplier/onboarding/bank-details`
- `POST /api/mobile/supplier/onboarding/submit`
- `GET /api/mobile/supplier/onboarding/status`
- `GET /api/mobile/supplier/onboarding/applications/{application}`
- `POST /api/mobile/kyc/email-verification`
- `POST /api/kyc/prembly/webhook`

Admin supplier review endpoints remain protected by Sanctum and the mobile admin middleware.

## Website Supplier KYC Test Page

The Laravel website route below now shows a standard supplier KYC form and submits into the same supplier onboarding service used by the mobile API:

- `GET /supplier-kyc.php`
- `POST /supplier-kyc.php`

The web form collects CAC/business registration details, contact details, BVN or NIN, ID document type, selfie, ID document, bank code, account number, and account name. On submit, Laravel runs the provider-backed business, identity, bank, and final decision checks through the provider-agnostic onboarding service.

Sensitive fields such as BVN, NIN, and full bank account number are not repopulated into the form after validation errors.

## Prembly Email Verification

The new email endpoint uses the shared KYC provider contract and calls Laravel only from the app/frontend. For Prembly, the configured endpoint is:

- `PREMBLY_ENDPOINT_EMAIL_COMPANY_SEARCH=/identitypass/verification/global/company/search_with_email`

This is Prembly's company search by email endpoint, not a general mailbox deliverability check. If the Prembly dashboard has a different sandbox product for direct email deliverability verification, update only the env/config endpoint mapping and adapter payload as needed.

## Required Environment Variables

- `KYC_PROVIDER=fake|prembly|dojah`
- `KYC_MODE=local|sandbox|production`
- `PREMBLY_API_KEY=`
- `PREMBLY_BASE_URL=https://api.prembly.com`
- `PREMBLY_APP_ID=`
- `PREMBLY_WEBHOOK_SECRET=`
- `PREMBLY_TIMEOUT_SECONDS=30`
- `PREMBLY_ENDPOINT_EMAIL_COMPANY_SEARCH=/identitypass/verification/global/company/search_with_email`

Keep `KYC_PROVIDER=fake` for local automated tests unless deliberately testing sandbox Prembly.

## Verification Notes

- Supplier-facing responses stay generic and do not expose internal reason codes.
- BVN, NIN, bank account numbers, selfie files, and ID documents are not stored raw in provider results.
- ID/selfie uploads use Laravel's private `local` disk.
- Prembly provider references are stored separately for audit/webhook matching.
- Webhook processing is idempotent and disabled unless `PREMBLY_WEBHOOK_SECRET` is configured.

## Commands Run

- `php artisan test`
- `php artisan route:list --path=kyc`
- `php artisan config:clear`
- `vendor/bin/pint --test` on touched PHP files
- `npm run typecheck`
- `git diff --check`

All checks passed when this note was prepared. `git diff --check` only reported existing line-ending warnings.
