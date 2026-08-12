# RentFlow MVP

PHP 8.3 + MySQL API, owner dashboard, deterministic availability and booking flow. Deploy with Railway using the included Dockerfile, add a MySQL service, set `MYSQL_URL`, import `database/schema.sql`, then connect n8n and Meta WhatsApp Cloud.

Endpoints: `GET /api/health`, `GET /api/vehicles/available?start=&end=&category=`, `POST /api/bookings`, `PATCH /api/bookings/{id}/status`, `GET /api/dashboard/stats`.
