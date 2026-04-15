<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);
        $currentUser = (array) $request->session()->get('legacy_user', []);
        $currentUserRole = (string) ($currentUser['role'] ?? 'builder');
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

        // Buyers mostly message suppliers, suppliers mostly message buyers.
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

        $contacts = $recipients->map(function ($recipient) use ($unreadBySender, $contactMeta) {
            $recipientId = (int) $recipient->id;
            $meta = $contactMeta[$recipientId] ?? null;
            $recipient->unread_count = (int) ($unreadBySender[$recipientId] ?? 0);
            $recipient->last_message = (string) ($meta['last_message'] ?? 'No conversation yet.');
            $recipient->last_message_at = $meta['last_message_at'] ?? null;

            return $recipient;
        })->sortByDesc(function ($recipient) {
            return (string) ($recipient->last_message_at ?? '1970-01-01 00:00:00');
        })->values();

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

        return view('messages.index', [
            'recipients' => $recipients,
            'contacts' => $contacts,
            'selectedContact' => $selectedContact,
            'selectedContactId' => $candidateContactId,
            'threadMessages' => $threadMessages,
            'unreadTotal' => $unreadTotal,
            'error' => '',
            'successCode' => (string) $request->query('success', ''),
            'preselectedReceiverId' => $candidateContactId,
            'materialId' => $materialId,
        ]);
    }

    public function store(Request $request)
    {
        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);
        $currentUser = (array) $request->session()->get('legacy_user', []);
        $currentUserRole = (string) ($currentUser['role'] ?? 'builder');
        $receiverId = (int) $request->input('receiver_id', 0);
        $content = trim((string) $request->input('content', ''));
        $materialId = (int) $request->input('material_id', 0);

        $redirectBase = '/messages.php';
        $redirectQuery = [];
        if ($receiverId > 0) {
            $redirectQuery[] = 'receiver_id=' . $receiverId;
        }
        if ($materialId > 0) {
            $redirectQuery[] = 'material_id=' . $materialId;
        }
        if ($redirectQuery !== []) {
            $redirectBase .= '?' . implode('&', $redirectQuery);
        }

        if ($receiverId <= 0 || $receiverId === $currentUserId || $content === '') {
            return redirect($redirectBase)->with('error', 'Please select a valid recipient and enter a message.');
        }

        if (mb_strlen($content) > 3000) {
            return redirect($redirectBase)->with('error', 'Message is too long. Please keep it under 3000 characters.');
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
            return redirect('/messages.php')->with('error', 'Selected recipient is not available for messaging.');
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

        return redirect('/messages.php?success=sent&contact_id=' . $receiverId);
    }
}
