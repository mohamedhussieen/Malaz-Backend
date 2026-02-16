---
description: Deploy Laravel backend to Namecheap shared hosting (backendlive.malaz-development.com)
---

# 🚀 Deploy Malaz Laravel Backend to Namecheap Shared Hosting

## Overview

| Item                  | Value                                          |
|-----------------------|------------------------------------------------|
| **Backend Subdomain** | `backendlive.malaz-development.com`            |
| **Admin Subdomain**   | `admin.malaz-development.com`                  |
| **Document Root**     | `/public_html/backendlive`                     |
| **App Location**      | `~/backendlive_app/` (outside public_html)     |
| **PHP Version**       | 8.2+ ✅                                        |
| **Database**          | MariaDB/MySQL                                  |

---

## Step 1: Secure File Structure (Crucial)

To deploy securely on cPanel, we separate the **app code** from the **public assets**.

### 1.1 Move App Code
Move the entire project (EXCEPT `public/` contents) to a folder **outside** `public_html`.
- Path: `/home/malatktf/backendlive_app/`

### 1.2 Move Public Assets
Move the contents of the `public/` folder to the subdomain's document root.
- Path: `/home/malatktf/public_html/backendlive/`

### 1.3 Fix `index.php`
Edit `/home/malatktf/public_html/backendlive/index.php` to point to the secure app folder:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Point to the secure app folder
if (file_exists($maintenance = __DIR__.'/../../backendlive_app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register Autoloader
require __DIR__.'/../../backendlive_app/vendor/autoload.php';

// Bootstrap App
(require_once __DIR__.'/../../backendlive_app/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

---

## Step 2: Database Setup & Permissions

### 2.1 Create Database & User
1. Go to **cPanel** → **MySQL® Databases**.
2. Create Database: `malatktf_prod`
3. Create User: `malatktf_admin`
4. **IMPORTANT: Add User to Database**
   - Scroll to "Add User To Database".
   - Select User and Database.
   - Click **Add**.
   - Check **ALL PRIVILEGES**.
   - Click **Make Changes**.
   *(Fixes `Access denied for user` error)*

### 2.2 Fix Migration Key Length
To fix `Specified key was too long` error, edit `app/Providers/AppServiceProvider.php` in the `boot` method:

```php
public function boot(): void
{
    // Fix for MariaDB older versions
    \Illuminate\Support\Facades\Schema::defaultStringLength(191);

    // ... existing code ...
}
```

---

## Step 3: Server Configuration (.env)

Edit `/home/malatktf/backendlive_app/.env`:

```env
APP_NAME=Malaz
APP_ENV=production
APP_DEBUG=true  # Set to false after testing
APP_URL=https://backendlive.malaz-development.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=malatktf_prod
DB_USERNAME=malatktf_admin
DB_PASSWORD=YOUR_STRONG_PASSWORD

# Fixes Login/Session Issues
SESSION_DRIVER=database
SESSION_DOMAIN=.malaz-development.com
SANCTUM_STATEFUL_DOMAINS=admin.malaz-development.com,backendlive.malaz-development.com
```

---

## Step 4: Final Commands

Run these in **cPanel Terminal**:

```bash
cd ~/backendlive_app

# 1. Install Dependencies
composer install --optimize-autoloader --no-dev

# 2. Run Migrations
php artisan migrate --force

# 3. Create Storage Link (Fixes 404 on images)
rm -f ~/public_html/backendlive/storage
ln -s ~/backendlive_app/storage/app/public ~/public_html/backendlive/storage

# 4. Clear Caches (Run this whenever you change .env)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Troubleshooting

| Error | Fix |
|-------|-----|
| `404 Not Found` | Check `index.php` paths and ensure `.htaccess` is in `public_html/backendlive`. |
| `Access denied for user 'root'` | Update `.env` with correct `malatktf_` username. |
| `Access denied for user 'malatktf_admin'` | Add User to Database in cPanel with **ALL PRIVILEGES**. |
| `Specified key was too long` | Add `Schema::defaultStringLength(191)` to `AppServiceProvider.php`. |
| `500 Internal Server Error` | Check `storage/logs/laravel.log` or set `APP_DEBUG=true`. |
