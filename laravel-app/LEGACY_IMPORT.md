# Legacy Data Import (One-Time)

This Laravel project includes a one-time importer command:

```bash
php artisan legacy:precheck
php artisan legacy:import --dry-run
php artisan legacy:import --truncate
```

## 1) Configure source and target databases in `.env`

- `DB_*` = **Laravel target database** (new app DB)
- `LEGACY_DB_*` = **legacy source database** (old app DB)

Example:

```dotenv
DB_DATABASE=naijabuilders
LEGACY_DB_DATABASE=naijabuilders_legacy
```

> Important: `LEGACY_DB_DATABASE` must be different from `DB_DATABASE`.

## 2) Run a dry run first

Run precheck first:

```bash
php artisan legacy:precheck
```

Then run dry run:

```bash
php artisan legacy:import --dry-run
```

This shows table counts without writing data.

## 3) Run the actual import

- Append/import only (keeps existing rows):

```bash
php artisan legacy:import
```

- Clean import (empties target tables first):

```bash
php artisan legacy:import --truncate
```

## Imported tables

- `users`
- `materials`
- `material_images`
- `orders`
- `order_items`
- `messages`

The importer uses ID-preserving inserts (`insertOrIgnore`) to avoid duplicate key errors when appending.
