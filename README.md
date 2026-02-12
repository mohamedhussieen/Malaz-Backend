# Malaz API (Laravel 11/12 compatible)

REST API backend for the Malaz corporate profile website. Frontend consumes APIs only.

**Base URL**
`/api/v1`

**Storage**
Media is stored on the `public` disk. Absolute URLs are generated via `Storage::disk('public')->url($path)` and depend on `APP_URL`.

## Setup
1. Install dependencies
```
composer install
```
2. Configure environment
```
cp .env.example .env
php artisan key:generate
```
Set `APP_URL` (required for absolute media URLs).

3. Migrate
```
php artisan migrate
```
4. Link storage
```
php artisan storage:link
```
5. Create an admin user (example)
```
php artisan tinker
>>> \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']);
```

## Auth (Admin)
Sanctum token auth.

### Login
`POST /api/v1/admin/auth/login`
```
curl -X POST http://localhost:8000/api/v1/admin/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"secret"}'
```

### Me
`GET /api/v1/admin/me`
```
curl http://localhost:8000/api/v1/admin/me \
  -H "Authorization: Bearer {token}"
```

### Logout
`POST /api/v1/admin/auth/logout`
```
curl -X POST http://localhost:8000/api/v1/admin/auth/logout \
  -H "Authorization: Bearer {token}"
```

## Public Endpoints
- `GET /api/v1/home`
- `GET /api/v1/owners`
- `GET /api/v1/projects`
- `GET /api/v1/projects/{id}`
- `GET /api/v1/platforms`
- `POST /api/v1/contact`

## Admin Endpoints (Auth: Bearer)
- Owners CRUD: `/api/v1/admin/owners`
- Projects CRUD: `/api/v1/admin/projects`
- Project Gallery: `POST /api/v1/admin/projects/{id}/gallery`
- Project Gallery Update: `PATCH /api/v1/admin/projects/{id}/gallery/{imageId}`
- Project Gallery Delete: `DELETE /api/v1/admin/projects/{id}/gallery/{imageId}`
- Home Content: `GET|PUT /api/v1/admin/home`
- Home Hero Gallery: `POST /api/v1/admin/home/hero-gallery`
- Home Hero Gallery Delete: `DELETE /api/v1/admin/home/hero-gallery/{imageId}`
- Platform Links: `GET /api/v1/admin/platform-links`
- Platform Upsert: `PUT /api/v1/admin/platform-links/{key}`
- Platform Toggle: `PATCH /api/v1/admin/platform-links/{id}/toggle`
- Contact Messages: `GET /api/v1/admin/contact-messages`
- Contact Message: `GET /api/v1/admin/contact-messages/{id}`
- Contact Message Status: `PATCH /api/v1/admin/contact-messages/{id}/status`
- Contact Message Delete: `DELETE /api/v1/admin/contact-messages/{id}`

## Notes
- Media fields store only relative paths in DB.
- Use `APP_URL` to generate absolute URLs.
- Rate limits: login and contact endpoints.
- Caching is enabled for public reads and invalidated on admin writes.
