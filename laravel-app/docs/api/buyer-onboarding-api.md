# NaijaBuilders Buyer Onboarding API

Base path: `/api/mobile`

All endpoints below require `Authorization: Bearer <token>` except registration and login.

## Buyer Registration

`POST /register`

Purpose: create a buyer or supplier account. Buyer signup stays minimal and does not force identity verification.

Request fields: `name`, `email`, `phone`, `password`, `confirm_password`, `terms_accepted`, optional `privacy_accepted`, `location`, `company`, `account_type`, `service_category`, `service_areas`, `device_fingerprint`.

Supported `account_type` values include `buyer`, `builder`, `supplier`, and `service_provider`. Service providers use the supplier workspace and can receive routed service requests after review.

Response fields: `token`, `user`, `otp.email`, `otp.phone`.

Mobile impact: app may show the returned local/test OTP code in development, then use `/otp/confirm`.

## OTP Confirmation

`POST /otp/send`

Request fields: `channel` as `email` or `phone`.

Response fields: `message`, `otp.channel`, `otp.expires_at`, optional `otp.debug_code` in local/testing.

`POST /otp/confirm`

Request fields: `channel`, `otp`.

Response fields: `message`, `channel`.

## Order Placement And Verification Tier Result

`POST /orders`

Purpose: place an order and run NGN-based tiering, first transaction rules, payment routing, gateway risk, and passive fraud monitoring.

Request fields: `supplier_id` plus `amount_ngn`, or `items[]`; `payment_method_type`; optional `payment`; `billing_name`; `billing_country`; `card_country`; `gateway_risk_level`; `delivery_address`; `delivery_country`; `recipient_name`; `recipient_phone`; optional `recipient_relationship`; optional `device_fingerprint`.

Response fields: `order_id`, `reference`, `verification_tier`, `verification_status`, `review_status`, `fraud_score`, `fraud_trigger_level`, `payment`, `gateway_risk`, `next_action`, `message`.

Mobile impact: when `next_action` is `submit_kyc`, show the buyer ID upload flow.

## KYC Submission

`POST /kyc/buyer/id-document`

Purpose: submit transaction-triggered Tier 2/Tier 3 identity evidence.

Request fields: multipart `document_type`, optional `verified_id_name`, optional `id_document`, optional `selfie`.

Response fields: `message`, `verification.id`, `verification.status`, `verification.provider`, `verification.provider_reference`, `verification.verified_id_name`, `verification.document_type`.

## Payment Risk Result

Payment risk is returned from `POST /orders` inside `gateway_risk`. High risk blocks, medium risk proceeds/passively logs for Tier 1, proceeds/flags for Tier 2, and holds Tier 3 for review. UK/EU/US card with Nigerian delivery and medium Stripe risk is treated as expected cross-border behavior unless risk is high.

## Delivery Recipient And Supplier Photo Upload

Delivery recipient fields are required at `POST /orders`: `recipient_name`, `recipient_phone`, optional `recipient_relationship`.

`POST /orders/{orderId}/delivery/photos`

Purpose: supplier uploads timestamped delivery proof before OTP can be generated.

Request fields: multipart `photo`, optional `gps_lat`, `gps_lng`.

Response fields: `message`, `photo.id`, `photo.path`, `photo.captured_at`, `photo.gps_lat`, `photo.gps_lng`.

## OTP Generation And Confirmation

`POST /orders/{orderId}/delivery/otp`

Purpose: supplier sends handover OTP to nominated recipient after minimum photo proof is uploaded.

Request fields: optional `gps_lat`, `gps_lng`.

Response fields: `message`, `recipient_phone`, optional `debug_code` in local/testing.

`POST /orders/{orderId}/delivery/otp/confirm`

Purpose: buyer/supplier confirms recipient handover code and opens the dispute window.

Request fields: `otp`.

Response fields: `message`, `dispute_window_ends_at`, `escrow_release_at`.

## Dispute Creation

`POST /orders/{orderId}/disputes`

Purpose: buyer raises a dispute during the configurable dispute window.

Request fields: `reason`.

Response fields: `message`, `dispute.id`, `dispute.status`, `dispute.created_at`.

## Admin Manual Review Actions

`GET /admin/buyer-reviews`

Purpose: admin manual review queue and flagged order filter.

Filters: `trigger_level`, `gateway_risk_level`, `min_fraud_score`, `max_fraud_score`, `min_order_value`, `max_order_value`, `max_account_age_hours`.

`GET /admin/buyer-reviews/{orderId}`

Purpose: case detail with buyer profile, order details, fraud score breakdown, trigger reason, device/IP data, transaction history, ID status, billing-name match, and audit trail.

`POST /admin/buyer-reviews/{orderId}/approve`

`POST /admin/buyer-reviews/{orderId}/reject`

`POST /admin/buyer-reviews/{orderId}/more-info`

Request fields: optional `notes`; `more-info` also accepts optional `message`.

Response fields: `message`, `order`.

Mobile/admin impact: more-info sends a buyer message with the configured deadline and every admin action writes `buyer_review_audits`.

## Service Requests

`GET /services/options`

Purpose: return supported construction service types and budget ranges for mobile request forms.

Response fields: `service_types[]`, `budget_ranges[]`.

`POST /services/requests`

Purpose: let authenticated buyers request an architect, engineer, project manager, contractor, surveyor, designer, or trade professional.

Request fields: `service_type`, `project_title`, `project_location`, `project_description`, `contact_name`, `contact_phone`, `contact_email`, optional `budget_range`, optional `preferred_start_date`.

Response fields: `message`, `service_request.id`, `service_request.service_type`, `service_request.status`, `service_request.created_at`.
