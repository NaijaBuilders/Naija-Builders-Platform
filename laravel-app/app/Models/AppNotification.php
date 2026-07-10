<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $fillable = [
        'user_id',
        'event_key',
        'title',
        'body',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Record an in-app notification for a user. Failures never interrupt the
     * action that triggered the notification.
     *
     * Named `record` (not `push`) because Eloquent's base Model already defines
     * a non-static push(); redeclaring it static is a fatal error.
     */
    public static function record(
        int $userId,
        string $eventKey,
        string $title,
        ?string $body = null,
        array $data = []
    ): void {
        try {
            static::query()->create([
                'user_id' => $userId,
                'event_key' => $eventKey,
                'title' => $title,
                'body' => $body,
                'data' => $data === [] ? null : $data,
            ]);
        } catch (\Throwable) {
            // Table may not exist yet or insert may fail; notifying is best-effort.
        }
    }
}
