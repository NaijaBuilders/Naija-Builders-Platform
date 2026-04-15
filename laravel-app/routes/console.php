<?php

use App\Services\LegacyDataImporter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('legacy:import {--dry-run : Preview import counts only} {--truncate : Truncate Laravel tables before import}', function (LegacyDataImporter $importer) {
    $dryRun = (bool) $this->option('dry-run');
    $truncate = (bool) $this->option('truncate');

    if (!$dryRun) {
        $this->warn('Ensure LEGACY_DB_DATABASE is set to your old DB and DB_DATABASE is set to Laravel target DB.');
    }

    try {
        $result = $importer->import($dryRun, $truncate);
    } catch (\Throwable $exception) {
        $this->error('Import failed: ' . $exception->getMessage());
        $this->line('Set LEGACY_DB_* in .env to your old database connection, then retry.');
        return self::FAILURE;
    }

    $this->info('Mode: ' . $result['mode']);
    $this->table(
        ['Table', 'Source Rows', 'Imported Rows'],
        collect($result['summary'])->map(function (array $row, string $table): array {
            return [$table, $row['source_count'], $row['imported']];
        })->values()->all()
    );

    if ($dryRun) {
        $this->comment('Dry run complete. Re-run without --dry-run to import data.');
    } else {
        $this->info('Legacy data import completed successfully.');
    }
    return self::SUCCESS;
})->purpose('One-time import from legacy database into Laravel schema');

Artisan::command('legacy:precheck', function () {
    $requiredTables = [
        'users',
        'materials',
        'material_images',
        'orders',
        'order_items',
        'messages',
    ];

    $targetConnection = (string) config('database.default');
    $targetDatabase = (string) config('database.connections.' . $targetConnection . '.database');
    $legacyDatabase = (string) config('database.connections.legacy_mysql.database');

    $this->line('Target connection: ' . $targetConnection . ' (DB: ' . ($targetDatabase !== '' ? $targetDatabase : '[empty]') . ')');
    $this->line('Legacy connection: legacy_mysql (DB: ' . ($legacyDatabase !== '' ? $legacyDatabase : '[empty]') . ')');

    if ($targetDatabase !== '' && $legacyDatabase !== '' && $targetDatabase === $legacyDatabase) {
        $this->error('DB_DATABASE and LEGACY_DB_DATABASE cannot point to the same database.');
        return self::FAILURE;
    }

    try {
        DB::connection($targetConnection)->select('SELECT 1');
        $this->info('Target DB connection: OK');
    } catch (\Throwable $exception) {
        $this->error('Target DB connection failed: ' . $exception->getMessage());
        return self::FAILURE;
    }

    try {
        DB::connection('legacy_mysql')->select('SELECT 1');
        $this->info('Legacy DB connection: OK');
    } catch (\Throwable $exception) {
        $this->error('Legacy DB connection failed: ' . $exception->getMessage());
        return self::FAILURE;
    }

    $rows = [];
    $hasMissing = false;

    foreach ($requiredTables as $table) {
        $legacyExists = Schema::connection('legacy_mysql')->hasTable($table);
        $targetExists = Schema::connection($targetConnection)->hasTable($table);

        if (!$legacyExists || !$targetExists) {
            $hasMissing = true;
        }

        $rows[] = [
            $table,
            $legacyExists ? 'yes' : 'no',
            $targetExists ? 'yes' : 'no',
        ];
    }

    $this->table(['Table', 'Legacy Exists', 'Target Exists'], $rows);

    if ($hasMissing) {
        $this->error('Precheck failed: one or more required tables are missing.');
        return self::FAILURE;
    }

    $this->info('Precheck passed. You can run: php artisan legacy:import --dry-run');
    return self::SUCCESS;
})->purpose('Validate legacy/target DB connections and required tables before import');
