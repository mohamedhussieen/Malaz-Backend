# Angular Frontend Handoff Prompt (New Backend Changes)

Use this as the source of truth for integrating the latest API changes in the Malaz project.

## Scope of new backend work

1. Blog module now supports **one blog with many related paragraph blocks**.
2. Added list search for **Owners** and **Projects**.
3. Contact-us submit now requires **phone** and supports `msg` alias.
4. Admin contact list now supports typed search by `phone`, `name`, `email`, `msg`.

## Base + auth

- Base URL: `/api/v1`
- Admin endpoints require Sanctum Bearer token:
  - `Authorization: Bearer <token>`
- Locale header supported:
  - `X-Lang: en` or `X-Lang: ar`

## 1) Blog + Paragraphs integration

### Endpoints

- Admin CRUD: `/api/v1/admin/blogs`
- Public list/detail: `/api/v1/blogs` and `/api/v1/blogs/{slug}`

### Create blog (admin)

`POST /api/v1/admin/blogs`

Payload (JSON):

```json
{
  "title_ar": "عنوان",
  "title_en": "Title",
  "excerpt_ar": "ملخص",
  "excerpt_en": "Excerpt",
  "is_published": true,
  "paragraphs": [
    {
      "header_ar": "مقدمة",
      "header_en": "Introduction",
      "content_ar": "نص عربي",
      "content_en": "English text",
      "sort_order": 1
    }
  ]
}
```

Rules:

- `paragraphs` is required on create.
- `paragraphs` must contain at least 1 item.
- Each paragraph requires both `content_ar` and `content_en`.

### Update blog (admin)

`PUT /api/v1/admin/blogs/{id}`

- If `paragraphs` is sent, backend **replaces all existing paragraphs** with the new list.
- Keep this in mind in edit UI: send full paragraph array, not partial diff.

### Blog detail response (admin/public)

`data.paragraphs` now exists:

```json
{
  "id": 1,
  "title": "...localized...",
  "slug": "...",
  "excerpt": "...localized...",
  "content": "...localized...",
  "paragraphs": [
    {
      "id": 10,
      "header": "...localized...",
      "content": "...localized...",
      "header_ar": "...",
      "header_en": "...",
      "content_ar": "...",
      "content_en": "...",
      "sort_order": 1
    }
  ]
}
```

## 2) Owners search

### Endpoints

- Public: `GET /api/v1/owners?search=<term>&per_page=10&page=1`
- Admin: `GET /api/v1/admin/owners?search=<term>&per_page=10&page=1`

### Search behavior

Searches in owner fields:

- `name`, `name_ar`, `name_en`
- `title`, `title_ar`, `title_en`

## 3) Projects search

### Endpoints

- Public: `GET /api/v1/projects?search=<term>&per_page=10&page=1`
- Admin: `GET /api/v1/admin/projects?search=<term>&per_page=10&page=1`

### Search behavior

Searches in project fields:

- `name`, `name_ar`, `name_en`
- `location`, `location_ar`, `location_en`
- `description`, `description_ar`, `description_en`

## 4) Contact-us changes

### Public submit

Endpoint: `POST /api/v1/contact`

Now required:

- `name`
- `email`
- `phone` (required now)

Optional:

- `whatsapp`
- `note`
- `msg` (alias for note)

If UI sends `msg`, backend stores it in `note`.

### Admin contact list typed search

Endpoint: `GET /api/v1/admin/contact-messages`

Query params:

- `search=<term>`
- `type=phone|name|email|msg`

Examples:

- `?type=phone&search=010`
- `?type=name&search=ahmed`
- `?type=email&search=@gmail.com`
- `?type=msg&search=quotation`

If `type` is missing or invalid and `search` exists, backend does broad search across phone/name/email/msg.

### Contact response shape

Admin contact item includes both:

- `note`
- `msg` (same value as note for frontend convenience)

## Frontend implementation notes

1. Blog editor UI should manage paragraph blocks as dynamic array (add/remove/reorder).
2. Blog edit submit should send full `paragraphs` array because update is replace-all.
3. Add search input to owners/projects list screens and send `search` param.
4. Contact-us form must not submit without `phone`.
5. Admin contact table filter should expose filter type selector (`phone/name/email/msg`) + search text.

## Quick QA checklist for Angular side

1. Create blog with 2 paragraphs -> detail returns 2 paragraphs.
2. Edit blog with 1 paragraph -> detail now returns 1 paragraph.
3. Owners search term narrows list.
4. Projects search term narrows list.
5. Contact submit without phone shows validation error.
6. Contact submit with msg persists and appears in admin list/details.
7. Admin contact typed searches return expected rows.
