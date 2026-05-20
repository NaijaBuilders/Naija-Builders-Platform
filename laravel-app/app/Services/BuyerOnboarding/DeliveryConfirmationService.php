<?php

namespace App\Services\BuyerOnboarding;

use App\Models\OrderDeliveryPhoto;
use App\Models\OrderDispute;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DeliveryConfirmationService
{
    public function __construct(
        private readonly BuyerOnboardingSettings $settings,
        private readonly BuyerOrderService $orders,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function storePhoto(User $supplier, int $orderId, array $data, Request $request): OrderDeliveryPhoto
    {
        $order = $this->supplierOrder($supplier, $orderId);
        $file = $request->file('photo');
        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'photo' => 'Upload a delivery photo.',
            ]);
        }

        $path = $file->store('delivery-proof/'.$order->id, 'local');
        if (! $path) {
            throw ValidationException::withMessages([
                'photo' => 'The delivery photo could not be stored.',
            ]);
        }

        DB::table('orders')->where('id', $order->id)->update([
            'delivery_status' => 'arriving',
            'delivery_gps_lat' => $data['gps_lat'] ?? $order->delivery_gps_lat,
            'delivery_gps_lng' => $data['gps_lng'] ?? $order->delivery_gps_lng,
            'updated_at' => now(),
        ]);

        return OrderDeliveryPhoto::query()->create([
            'order_id' => $order->id,
            'uploaded_by' => $supplier->id,
            'path' => $path,
            'captured_at' => now(),
            'gps_lat' => $data['gps_lat'] ?? null,
            'gps_lng' => $data['gps_lng'] ?? null,
            'metadata' => [
                'source' => 'supplier_delivery_upload',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function generateOtp(User $supplier, int $orderId, array $data = []): array
    {
        $order = $this->supplierOrder($supplier, $orderId);
        $photoCount = OrderDeliveryPhoto::query()->where('order_id', $order->id)->count();
        if ($photoCount < $this->settings->deliveryMinimumPhotos()) {
            throw ValidationException::withMessages([
                'photos' => 'Upload delivery photos before sending the handover code.',
            ]);
        }

        $code = (string) random_int(100000, 999999);
        DB::table('orders')->where('id', $order->id)->update([
            'delivery_status' => 'otp_sent',
            'delivery_otp_hash' => Hash::make($code),
            'delivery_otp_generated_at' => now(),
            'delivery_gps_lat' => $data['gps_lat'] ?? $order->delivery_gps_lat,
            'delivery_gps_lng' => $data['gps_lng'] ?? $order->delivery_gps_lng,
            'updated_at' => now(),
        ]);

        return [
            'message' => 'Delivery code sent to the nominated recipient.',
            'recipient_phone' => (string) $order->recipient_phone,
            'debug_code' => $this->shouldExposeCode() ? $code : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function confirmOtp(User $actor, int $orderId, string $code): array
    {
        $order = $this->buyerOrSupplierOrder($actor, $orderId);
        $hash = (string) ($order->delivery_otp_hash ?? '');
        $generatedAt = $order->delivery_otp_generated_at ? CarbonImmutable::parse($order->delivery_otp_generated_at) : null;

        if ($hash === '' || ! $generatedAt || now()->greaterThan($generatedAt->copy()->addMinutes($this->settings->deliveryOtpTtlMinutes()))) {
            throw ValidationException::withMessages([
                'otp' => 'The delivery code has expired. Ask the supplier to send a new code.',
            ]);
        }

        if (! Hash::check($code, $hash)) {
            throw ValidationException::withMessages([
                'otp' => 'The delivery code is not correct.',
            ]);
        }

        $windowEndsAt = now()->addHours($this->settings->disputeWindowHours());
        DB::table('orders')->where('id', $order->id)->update([
            'order_status' => 'completed',
            'delivery_status' => 'confirmed',
            'delivery_otp_confirmed_at' => now(),
            'delivery_otp_hash' => null,
            'dispute_status' => 'window_open',
            'dispute_window_ends_at' => $windowEndsAt,
            'escrow_release_at' => $windowEndsAt,
            'updated_at' => now(),
        ]);

        $this->orders->markCompleted((int) $order->id);

        return [
            'message' => 'Delivery confirmed.',
            'dispute_window_ends_at' => $windowEndsAt->toISOString(),
            'escrow_release_at' => $windowEndsAt->toISOString(),
        ];
    }

    public function raiseDispute(User $buyer, int $orderId, string $reason): OrderDispute
    {
        $order = DB::table('orders')
            ->where('id', $orderId)
            ->where('buyer_id', $buyer->id)
            ->first();

        if (! $order) {
            throw ValidationException::withMessages(['order' => 'Order not found.']);
        }

        if (! $order->dispute_window_ends_at || now()->greaterThan(CarbonImmutable::parse($order->dispute_window_ends_at))) {
            throw ValidationException::withMessages([
                'reason' => 'The dispute window has closed for this order.',
            ]);
        }

        DB::table('orders')->where('id', $order->id)->update([
            'dispute_status' => 'open',
            'escrow_release_at' => null,
            'updated_at' => now(),
        ]);

        return OrderDispute::query()->create([
            'order_id' => $order->id,
            'user_id' => $buyer->id,
            'reason' => $reason,
            'status' => 'open',
        ]);
    }

    private function supplierOrder(User $supplier, int $orderId): object
    {
        $order = DB::table('orders')
            ->where('id', $orderId)
            ->where('supplier_id', $supplier->id)
            ->first();

        if (! $order) {
            throw ValidationException::withMessages(['order' => 'Order not found.']);
        }

        return $order;
    }

    private function buyerOrSupplierOrder(User $actor, int $orderId): object
    {
        $order = DB::table('orders')
            ->where('id', $orderId)
            ->where(function ($query) use ($actor): void {
                $query->where('buyer_id', $actor->id)
                    ->orWhere('supplier_id', $actor->id);
            })
            ->first();

        if (! $order) {
            throw ValidationException::withMessages(['order' => 'Order not found.']);
        }

        return $order;
    }

    private function shouldExposeCode(): bool
    {
        return (bool) config('buyer_onboarding.otp.expose_in_local', true)
            && app()->environment(['local', 'testing']);
    }
}
