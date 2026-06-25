<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'scope_type',
        'scope_id',
        'namespace',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Resolve a single setting value for a scope, with a fallback default.
     */
    public static function resolve(string $scopeType, int $scopeId, string $namespace, string $key, mixed $default = null): mixed
    {
        $row = static::query()
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->where('namespace', $namespace)
            ->where('key', $key)
            ->first();

        return $row ? $row->value : $default;
    }

    /**
     * Create or update a setting value for a scope.
     */
    public static function put(string $scopeType, int $scopeId, string $namespace, string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            [
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'namespace' => $namespace,
                'key' => $key,
            ],
            ['value' => $value]
        );
    }
}
