# MySQL-Only Local and cPanel Deployment Guide

This project is configured to run with MySQL only.
If MySQL is down, the app should fail fast.

## 1. Local Development (WAMP + MySQL)

1. Start WAMP and ensure `wampmysqld64` is running.
2. Create database `naijabuilders` in phpMyAdmin.
3. In `laravel-app/.env`, use:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8080

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=naijabuilders
DB_USERNAME=root
DB_PASSWORD=
```

4. Run:

```bash
php artisan config:clear
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8080
```

5. Open `http://127.0.0.1:8080`.

## 2. Important Note About Production Database

Use the same database engine (MySQL), but do not use your local database instance directly in production.

Recommended approach:
- Local: `naijabuilders` on your WAMP machine.
- Production: a separate cPanel MySQL database (for example `cpaneluser_naijabuilders`).

This is safer, faster, and avoids exposing your local machine to the internet.

## 3. cPanel Production Setup

1. Create a MySQL database and user in cPanel.
2. Grant the user all privileges on that database.
3. Upload project files to server.
4. Point document root to Laravel `public` directory.
5. In production `.env`, set:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpanel_db_name
DB_USERNAME=cpanel_db_user
DB_PASSWORD=cpanel_db_password
```

6. Run on server:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force
```

## 4. Move Local Data to Production (Optional)

If you want your tested local data on production:

1. Export local DB from phpMyAdmin (`naijabuilders.sql`).
2. Import into cPanel production database.
3. Confirm app works with production `.env`.

## 5. Quick Troubleshooting

- `SQLSTATE[HY000] [2002]`: MySQL service is not reachable or port is wrong.
- Blank/old settings: run `php artisan config:clear`.
- App runs but DB routes fail: verify `.env` database credentials and privileges.
