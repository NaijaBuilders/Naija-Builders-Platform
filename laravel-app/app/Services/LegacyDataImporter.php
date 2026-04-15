<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class LegacyDataImporter
{
    private const TABLES = [
        'users',
        'materials',
        'material_images',
        'orders',
        'order_items',
        'messages',
    ];

    private const TRUNCATE_ORDER = [
        'order_items',
        'orders',
        'material_images',
        'messages',
        'materials',
        'users',
    ];

    public function import(bool $dryRun = false, bool $truncate = false): array
    {
        $source = DB::connection('legacy_mysql');
        $target = DB::connection(config('database.default'));

        $sourceDb = (string) config('database.connections.legacy_mysql.database');
        $targetDb = (string) config('database.connections.' . config('database.default') . '.database');

        if ($sourceDb !== '' && $targetDb !== '' && $sourceDb === $targetDb) {
            throw new RuntimeException('LEGACY_DB_DATABASE must be different from DB_DATABASE before running import.');
        }

        $summary = [];

        foreach (self::TABLES as $table) {
            $summary[$table] = [
                'source_count' => (int) $source->table($table)->count(),
                'imported' => 0,
            ];
        }

        if ($dryRun) {
            return [
                'mode' => 'dry-run',
                'summary' => $summary,
            ];
        }

        $target->beginTransaction();

        try {
            $target->statement('SET FOREIGN_KEY_CHECKS=0');

            if ($truncate) {
                foreach (self::TRUNCATE_ORDER as $table) {
                    $target->table($table)->truncate();
                }
            }

            foreach (self::TABLES as $table) {
                $lastId = 0;

                while (true) {
                    $rows = $source->table($table)
                        ->where('id', '>', $lastId)
                        ->orderBy('id')
                        ->limit(500)
                        ->get();

                    if ($rows->isEmpty()) {
                        break;
                    }

                    $payload = [];
                    foreach ($rows as $row) {
                        $item = (array) $row;
                        $payload[] = $item;
                        $lastId = (int) $item['id'];
                    }

                    $summary[$table]['imported'] += $target->table($table)->insertOrIgnore($payload);
                }
            }

            $target->statement('SET FOREIGN_KEY_CHECKS=1');
            $target->commit();
        } catch (\Throwable $exception) {
            $target->rollBack();
            $target->statement('SET FOREIGN_KEY_CHECKS=1');
            throw $exception;
        }

        return [
            'mode' => $truncate ? 'truncate-and-import' : 'import-append',
            'summary' => $summary,
        ];
    }
}
