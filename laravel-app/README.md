# NaijaBuilders

NaijaBuilders is a Construction Materials Marketplace for Buyers and Suppliers

## Tech Stack

- PHP 8.2+
- Laravel 12
- MySQL (primary database + legacy source)
- Vite + Tailwind CSS

## Requirements

- PHP 8.2+ with required extensions for Laravel
- Composer
- Node.js 18+ and npm
- MySQL 8+ (or compatible)

## Setup

1) Install dependencies

```
composer install
npm install
```

2) Create the environment file

```
copy .env.example .env
```

3) Configure `.env`

- Set `APP_URL` for your local host
- Set `DB_*` for your main database
- Set `LEGACY_DB_*` if you plan to import legacy data

4) Generate the application key

```
php artisan key:generate
```

5) Run migrations

```
php artisan migrate
```

6) Build assets

```
npm run build
```

## Local Development

You can run the PHP server and Vite dev server together:

```
composer run dev
```

Or run them separately:

```
php artisan serve --port=8080
npm run dev
```

## Tests

```
composer run test
```

## Legacy Import

See `LEGACY_IMPORT.md` for import guidance and required legacy database settings.

## Deployment Notes

See `DEPLOYMENT_MYSQL_ONLY.md` for environment-specific setup.

## cPanel Deployment

This section covers a simple GitHub -> cPanel workflow. The `artisan` file must be in your repo and is required for Laravel to run.

### First-Time Setup (cPanel)

1) Upload the project to the server (via Git or File Manager)

- Recommended: use cPanel Git Version Control to clone your GitHub repo into a folder like `naijabuilders/`.
- If Git is not available, upload a zip from GitHub and extract it.

2) Point your domain to the `public/` directory

- If your docroot is `public_html`, set the document root to `naijabuilders/public`.

3) Create and configure `.env`

- Copy `.env.example` to `.env` and set production values.
- Set `APP_ENV=production`, `APP_DEBUG=false`.

4) Install PHP dependencies

From cPanel Terminal (or SSH):

```
composer install --no-dev --optimize-autoloader
```

5) Generate app key and caches

```
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

6) Run migrations (if needed)

```
php artisan migrate --force
```

7) Assets

- Preferred: run `npm run build` locally and upload the `public/build` folder.
- If Node is available on the server, you can run `npm install` and `npm run build` there.

8) Permissions

- Ensure `storage/` and `bootstrap/cache/` are writable by the web server.

### Updates (VS Code -> GitHub -> cPanel)

1) Commit and push from VS Code:

```
git add .
git commit -m "Your update"
git push
```

2) On cPanel, pull the latest code:

- If using cPanel Git: click Pull in Git Version Control.
- If using SSH: run `git pull` inside the project folder.

3) Reinstall optimized dependencies and rebuild caches:

```
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4) Run migrations if the update includes new database changes:

```
php artisan migrate --force
```

5) Upload new `public/build` if your frontend assets changed.
