# Mobile API Map

This folder mirrors the mobile API routes generated in the Laravel app.

Base path: /api/mobile

Public endpoints:
- POST /login
- POST /register
- GET /materials
- GET /materials/{materialId}
- GET /terms
- GET /support
- GET /forgot-password

Authenticated endpoints:
- GET /user
- POST /logout
- GET /dashboard
- GET /dashboard/analysis
- GET /dashboard/buyer
- POST /materials/{materialId}/review
- POST /suppliers/{supplierId}/review
- GET /listings
- POST /listings
- DELETE /listings/{listingId}
- GET /cart
- POST /cart/items
- PUT /cart/items/{materialId}
- DELETE /cart/items/{materialId}
- GET /messages
- POST /messages
- GET /profile
- POST /profile
- GET /settings
- POST /settings
- GET /saved-products
- POST /saved-products
- DELETE /saved-products/{materialId}
- GET /subscription
- POST /subscription
