<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use App\Models\Setting;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    /**
     * Catalog of notification events and their default channel state.
     * Channels: push, email, sms, in_app.
     */
    private const EVENTS = [
        'order_placed'     => ['push' => true,  'email' => true,  'sms' => false, 'in_app' => true],
        'order_status'     => ['push' => true,  'email' => false, 'sms' => false, 'in_app' => true],
        'delivery_update'  => ['push' => true,  'email' => false, 'sms' => false, 'in_app' => true],
        'delivery_confirm' => ['push' => true,  'email' => false, 'sms' => true,  'in_app' => true],
        'rfq_received'     => ['push' => true,  'email' => true,  'sms' => false, 'in_app' => true],
        'new_quote'        => ['push' => true,  'email' => true,  'sms' => false, 'in_app' => true],
        'quote_status'     => ['push' => true,  'email' => false, 'sms' => false, 'in_app' => true],
        'invoice_issued'   => ['push' => false, 'email' => true,  'sms' => false, 'in_app' => true],
        'payment_received' => ['push' => true,  'email' => true,  'sms' => false, 'in_app' => true],
        'payment_due'      => ['push' => true,  'email' => true,  'sms' => true,  'in_app' => true],
        'new_message'      => ['push' => true,  'email' => false, 'sms' => false, 'in_app' => true],
        'price_drop'       => ['push' => true,  'email' => false, 'sms' => false, 'in_app' => true],
        'back_in_stock'    => ['push' => true,  'email' => false, 'sms' => false, 'in_app' => true],
        'marketing'        => ['push' => false, 'email' => true,  'sms' => false, 'in_app' => false],
    ];

    private const DEFAULT_QUIET_HOURS = [
        'enabled' => false,
        'from' => '22:00',
        'to' => '07:00',
    ];

    public function index(Request $request)
    {
        $userId = (int) $request->user()->id;
        $saved = NotificationPreference::query()
            ->where('user_id', $userId)
            ->get()
            ->keyBy('event_key');

        $preferences = [];
        foreach (self::EVENTS as $key => $defaults) {
            $row = $saved->get($key);
            $preferences[] = [
                'event_key' => $key,
                'push'   => $row ? (bool) $row->push   : $defaults['push'],
                'email'  => $row ? (bool) $row->email  : $defaults['email'],
                'sms'    => $row ? (bool) $row->sms    : $defaults['sms'],
                'in_app' => $row ? (bool) $row->in_app : $defaults['in_app'],
            ];
        }

        $quiet = (array) Setting::resolve('user', $userId, 'notifications', 'quiet_hours', self::DEFAULT_QUIET_HOURS);

        return response()->json([
            'preferences' => $preferences,
            'quiet_hours' => [
                'enabled' => (bool) ($quiet['enabled'] ?? false),
                'from' => (string) ($quiet['from'] ?? self::DEFAULT_QUIET_HOURS['from']),
                'to' => (string) ($quiet['to'] ?? self::DEFAULT_QUIET_HOURS['to']),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $userId = (int) $request->user()->id;

        foreach ((array) $request->input('preferences', []) as $pref) {
            $key = (string) ($pref['event_key'] ?? '');
            if (! array_key_exists($key, self::EVENTS)) {
                continue;
            }

            NotificationPreference::query()->updateOrCreate(
                ['user_id' => $userId, 'event_key' => $key],
                [
                    'push'   => (bool) ($pref['push'] ?? false),
                    'email'  => (bool) ($pref['email'] ?? false),
                    'sms'    => (bool) ($pref['sms'] ?? false),
                    'in_app' => (bool) ($pref['in_app'] ?? false),
                ]
            );
        }

        if ($request->has('quiet_hours')) {
            $quiet = (array) $request->input('quiet_hours', []);
            Setting::put('user', $userId, 'notifications', 'quiet_hours', [
                'enabled' => (bool) ($quiet['enabled'] ?? false),
                'from' => (string) ($quiet['from'] ?? self::DEFAULT_QUIET_HOURS['from']),
                'to' => (string) ($quiet['to'] ?? self::DEFAULT_QUIET_HOURS['to']),
            ]);
        }

        return $this->index($request);
    }
}
