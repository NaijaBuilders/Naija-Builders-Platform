<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Support\NameFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $currentUserId = (int) $request->user()->id;
        $currentUserRole = (string) DB::table('users')->where('id', $currentUserId)->value('role');
        $preselectedReceiverId = (int) $request->query('receiver_id', 0);
        $selectedContactId = (int) $request->query('contact_id', 0);
        $materialId = (int) $request->query('material_id', 0);

        if ($preselectedReceiverId === $currentUserId) {
            $preselectedReceiverId = 0;
        }

        if ($selectedContactId === $currentUserId) {
            $selectedContactId = 0;
        }

        $recipientsQuery = DB::table('users')
            ->select(['id', 'full_name', 'company', 'role', 'profile_image_path'])
            ->where('id', '<>', $currentUserId)
            ->orderBy('full_name');

        if ($currentUserRole === 'supplier') {
            $recipientsQuery->whereIn('role', ['builder', 'admin']);
        } else {
            $recipientsQuery->whereIn('role', ['supplier', 'admin']);
        }

        $recipients = $recipientsQuery->get();
        $recipientIds = $recipients->pluck('id')->map(fn ($id) => (int) $id)->all();

        $candidateContactId = $selectedContactId > 0 ? $selectedContactId : $preselectedReceiverId;
        if ($candidateContactId > 0 && !in_array($candidateContactId, $recipientIds, true)) {
            $candidateContactId = 0;
        }

        if ($candidateContactId === 0 && count($recipientIds) > 0) {
            $candidateContactId = (int) $recipientIds[0];
        }

        if ($candidateContactId > 0) {
            $markReadPayload = ['is_read' => 1];
            if (Schema::hasColumn('messages', 'updated_at')) {
                $markReadPayload['updated_at'] = now();
            }

            DB::table('messages')
                ->where('receiver_id', $currentUserId)
                ->where('sender_id', $candidateContactId)
                ->where('is_read', 0)
                ->update($markReadPayload);
        }

        $unreadBySender = DB::table('messages')
            ->select(['sender_id', DB::raw('COUNT(*) as total')])
            ->where('receiver_id', $currentUserId)
            ->where('is_read', 0)
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $recentMessages = DB::table('messages')
            ->select(['id', 'sender_id', 'receiver_id', 'content', 'created_at'])
            ->where(function ($query) use ($currentUserId): void {
                $query->where('sender_id', $currentUserId)
                    ->orWhere('receiver_id', $currentUserId);
            })
            ->orderByDesc('created_at')
            ->get();

        // Build the conversation list from people the user has actually
        // exchanged messages with. The full `recipients` directory is only
        // used to start a NEW chat, not to populate the inbox.
        $contactMeta = [];
        foreach ($recentMessages as $message) {
            $otherUserId = (int) ($message->sender_id === $currentUserId ? $message->receiver_id : $message->sender_id);
            if (!in_array($otherUserId, $recipientIds, true)) {
                continue;
            }

            if (!array_key_exists($otherUserId, $contactMeta)) {
                $contactMeta[$otherUserId] = [
                    'last_message' => (string) $message->content,
                    'last_message_at' => $message->created_at,
                ];
            }
        }

        // If the user EXPLICITLY opened a specific contact (from a product/RFQ)
        // that they have not messaged yet, keep that one contact visible so the
        // thread can start. A bare inbox load ($explicitContact === false) shows
        // only real conversations, never an auto-selected phantom.
        $explicitContact = $selectedContactId > 0 || $preselectedReceiverId > 0;
        $conversationIds = array_keys($contactMeta);
        if (
            $explicitContact
            && $candidateContactId > 0
            && !in_array($candidateContactId, $conversationIds, true)
        ) {
            $conversationIds[] = $candidateContactId;
        }

        $recipientsById = $recipients->keyBy('id');

        $contacts = collect($conversationIds)
            ->map(function ($contactId) use ($recipientsById, $unreadBySender, $contactMeta) {
                $recipient = $recipientsById->get($contactId);
                if (!$recipient) {
                    return null;
                }

                $meta = $contactMeta[$contactId] ?? null;
                $recipient->unread_count = (int) ($unreadBySender[$contactId] ?? 0);
                $recipient->last_message = (string) ($meta['last_message'] ?? 'Start the conversation.');
                $recipient->last_message_at = $meta['last_message_at'] ?? null;

                return $recipient;
            })
            ->filter()
            ->sortByDesc(function ($recipient) {
                return (string) ($recipient->last_message_at ?? '1970-01-01 00:00:00');
            })
            ->values();

        $threadMessages = collect();
        if ($candidateContactId > 0) {
            $threadMessages = DB::table('messages as m')
                ->select(['m.id', 'm.sender_id', 'm.receiver_id', 'm.content', 'm.created_at'])
                ->where(function ($query) use ($currentUserId, $candidateContactId): void {
                    $query->where(function ($inner) use ($currentUserId, $candidateContactId): void {
                        $inner->where('m.sender_id', $currentUserId)
                            ->where('m.receiver_id', $candidateContactId);
                    })->orWhere(function ($inner) use ($currentUserId, $candidateContactId): void {
                        $inner->where('m.sender_id', $candidateContactId)
                            ->where('m.receiver_id', $currentUserId);
                    });
                })
                ->orderBy('m.created_at')
                ->get();
        }

        $selectedContact = $contacts->first(fn ($contact) => (int) $contact->id === $candidateContactId);
        $unreadTotal = $contacts->sum(fn ($contact) => (int) ($contact->unread_count ?? 0));

        return response()->json([
            'recipients' => $recipients,
            'contacts' => $contacts,
            'selected_contact' => $selectedContact,
            'selected_contact_id' => $candidateContactId,
            'thread_messages' => $threadMessages,
            'unread_total' => $unreadTotal,
            'material_id' => $materialId,
        ]);
    }

    public function store(Request $request)
    {
        $currentUserId = (int) $request->user()->id;
        $currentUserRole = (string) DB::table('users')->where('id', $currentUserId)->value('role');
        $receiverId = (int) $request->input('receiver_id', 0);
        $content = trim((string) $request->input('content', ''));
        $materialId = (int) $request->input('material_id', 0);

        if ($receiverId <= 0 || $receiverId === $currentUserId || $content === '') {
            return response()->json(['message' => 'Please select a valid recipient and enter a message.'], 422);
        }

        if (mb_strlen($content) > 3000) {
            return response()->json(['message' => 'Message is too long.'], 422);
        }

        $recipientQuery = DB::table('users')
            ->select(['id', 'role'])
            ->where('id', $receiverId)
            ->where('id', '<>', $currentUserId);

        if ($currentUserRole === 'supplier') {
            $recipientQuery->whereIn('role', ['builder', 'admin']);
        } else {
            $recipientQuery->whereIn('role', ['supplier', 'admin']);
        }

        $recipientExists = $recipientQuery->exists();
        if (!$recipientExists) {
            return response()->json(['message' => 'Selected recipient is not available for messaging.'], 422);
        }

        $payload = [
            'sender_id' => $currentUserId,
            'receiver_id' => $receiverId,
            'content' => $content,
            'is_read' => 0,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('messages', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('messages')->insert($payload);

        $sender = DB::table('users')->where('id', $currentUserId)->first(['full_name', 'company']);
        $senderName = trim((string) ($sender->company ?? '')) ?:
            NameFormatter::title((string) ($sender->full_name ?? 'A NaijaBuilders user'));

        AppNotification::record(
            $receiverId,
            'new_message',
            'New message from '.$senderName,
            mb_substr($content, 0, 120),
            ['contact_id' => (string) $currentUserId]
        );

        return response()->json([
            'message' => 'Message sent.',
            'receiver_id' => $receiverId,
            'material_id' => $materialId,
        ], 201);
    }
}
