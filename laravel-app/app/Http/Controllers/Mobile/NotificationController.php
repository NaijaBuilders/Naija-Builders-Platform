<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('app_notifications')) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }

        $userId = (int) $request->user()->id;

        $notifications = AppNotification::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (AppNotification $notification): array => [
                'id' => (string) $notification->id,
                'event_key' => $notification->event_key,
                'title' => $notification->title,
                'body' => $notification->body,
                'data' => $notification->data ?? [],
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->toISOString(),
            ]);

        $unreadCount = AppNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead(Request $request)
    {
        if (! Schema::hasTable('app_notifications')) {
            return response()->json(['message' => 'Notifications are not available yet.']);
        }

        $validated = $request->validate([
            'notification_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = AppNotification::query()
            ->where('user_id', (int) $request->user()->id)
            ->whereNull('read_at');

        if (! empty($validated['notification_id'])) {
            $query->where('id', (int) $validated['notification_id']);
        }

        $query->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications marked as read.']);
    }

    public function registerDevice(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:191'],
            'platform' => ['nullable', 'in:ios,android,web,unknown'],
        ]);

        if (! Schema::hasTable('device_tokens')) {
            return response()->json(['message' => 'Device registration is not available yet.']);
        }

        DB::table('device_tokens')->updateOrInsert(
            ['token' => $validated['token']],
            [
                'user_id' => (int) $request->user()->id,
                'platform' => $validated['platform'] ?? 'unknown',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['message' => 'Device registered for push notifications.']);
    }
}
