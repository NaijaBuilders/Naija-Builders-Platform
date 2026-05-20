<?php

namespace App\Services\BuyerOnboarding;

use App\Models\BuyerReviewAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AdminBuyerReviewService
{
    public function __construct(private readonly BuyerOnboardingSettings $settings) {}

    public function approve(int $orderId, User $reviewer, ?string $notes = null): object
    {
        return $this->decision($orderId, $reviewer, 'ADMIN_APPROVED', 'approved', 'processing', $notes, null);
    }

    public function reject(int $orderId, User $reviewer, ?string $notes = null): object
    {
        return $this->decision($orderId, $reviewer, 'ADMIN_REJECTED', 'rejected', 'cancelled', $notes, null);
    }

    public function requestMoreInfo(int $orderId, User $reviewer, ?string $message, ?string $notes = null): object
    {
        $deadline = now()->addHours($this->settings->moreInfoDeadlineHours());
        $message = $message ?: 'We need a little more information before completing this order.';
        $order = $this->decision($orderId, $reviewer, 'MORE_INFO_REQUESTED', 'more_info_requested', 'pending', $notes, $message, $deadline);

        if (Schema::hasTable('messages')) {
            DB::table('messages')->insert([
                'sender_id' => $reviewer->id,
                'receiver_id' => $order->buyer_id,
                'content' => $message.' Please respond within '.$this->settings->moreInfoDeadlineHours().' hours.',
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $order;
    }

    private function decision(int $orderId, User $reviewer, string $action, string $verificationStatus, string $orderStatus, ?string $notes, ?string $buyerMessage, mixed $deadline = null): object
    {
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (! $order) {
            throw ValidationException::withMessages(['order' => 'Order not found.']);
        }

        DB::table('orders')->where('id', $orderId)->update([
            'verification_status' => $verificationStatus,
            'review_status' => strtolower($action),
            'order_status' => $orderStatus,
            'manual_review_deadline_at' => $deadline,
            'updated_at' => now(),
        ]);

        BuyerReviewAudit::query()->create([
            'order_id' => $orderId,
            'user_id' => $order->buyer_id,
            'reviewer_id' => $reviewer->id,
            'operator_name' => (string) $reviewer->full_name,
            'action' => $action,
            'previous_status' => (string) $order->verification_status,
            'new_status' => $verificationStatus,
            'trigger_level' => $order->fraud_trigger_level,
            'reason_codes' => $this->jsonArray($order->fraud_triggers ?? null),
            'notes' => $notes,
            'buyer_message' => $buyerMessage,
            'deadline_at' => $deadline,
        ]);

        return DB::table('orders')->where('id', $orderId)->first();
    }

    /**
     * @return array<int, mixed>
     */
    private function jsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }
}
