@php($pageTitle = 'Messages')
@extends('layouts.app')

@section('content')
<div class="container page-shell messages-page">
    <div class="messages-page-header messages-header">
        <h1 class="title-reset">Messages</h1>
        <div class="messages-header-actions">
            <span class="text-note">Unread: {{ number_format((int) ($unreadTotal ?? 0)) }}</span>
            <a href="{{ session('legacy_user.role', '') === 'supplier' ? '/dashboard.php' : '/buyer-dashboard.php' }}" class="btn btn-outline">Back to Dashboard</a>
        </div>
    </div>

    @if (($successCode ?? '') === 'sent')
        <div class="alert alert-success">Message sent successfully.</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-md">
        <div class="card-body" style="padding-bottom: 0.5rem;">
            <form method="GET" action="/messages.php" class="messages-toolbar-form">
                <label for="start-chat-with" style="margin: 0;" class="text-note">Start / Switch Chat:</label>
                <select id="start-chat-with" name="contact_id" style="min-width: 220px;">
                    <option value="">Select contact</option>
                    @foreach ($recipients as $recipient)
                        <option value="{{ (int) $recipient->id }}" {{ (int) ($selectedContactId ?? 0) === (int) $recipient->id ? 'selected' : '' }}>
                            {{ \App\Support\NameFormatter::title((string) $recipient->full_name) }} ({{ $recipient->role }})
                        </option>
                    @endforeach
                </select>
                @if ((int) ($materialId ?? 0) > 0)
                    <input type="hidden" name="material_id" value="{{ (int) $materialId }}">
                @endif
                <button type="submit" class="btn btn-primary">Open Chat</button>
            </form>
        </div>
    </div>

    <div class="grid-3 gap-lg messages-page-layout" style="align-items: stretch;">
        <div class="card" style="height: 100%;">
            <div class="card-header"><h3 style="margin: 0; font-size: 1.05rem;">Conversations</h3></div>
            <div class="card-body" style="padding-top: 0.35rem; max-height: 560px; overflow-y: auto;">
                @forelse (($contacts ?? collect()) as $contact)
                    @php($isActive = (int) ($selectedContactId ?? 0) === (int) $contact->id)
                    @php($contactName = \App\Support\NameFormatter::title((string) $contact->full_name))
                    <a href="/messages.php?contact_id={{ (int) $contact->id }}" class="messages-contact-item {{ $isActive ? 'messages-contact-item-active' : '' }}" style="display: block; text-decoration: none; color: inherit; border: 1px solid {{ $isActive ? 'var(--primary-color)' : 'var(--neutral-200)' }}; border-radius: var(--rounded-md); padding: 0.65rem; margin-bottom: 0.55rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            @if (($contact->profile_image_path ?? '') !== '')
                                <img src="/{{ $contact->profile_image_path }}" alt="{{ $contactName }}" style="width: 38px; height: 38px; border-radius: 999px; object-fit: cover; border: 1px solid var(--neutral-200); flex-shrink: 0;">
                            @else
                                <span style="width: 38px; height: 38px; border-radius: 999px; background: var(--neutral-100); border: 1px solid var(--neutral-200); display: inline-flex; align-items: center; justify-content: center; color: var(--neutral-700); font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">{{ strtoupper(substr($contactName, 0, 1)) }}</span>
                            @endif
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; margin-bottom: 0.2rem;">
                                    <strong style="font-size: 0.95rem; color: var(--neutral-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $contactName }}</strong>
                                    @if ((int) ($contact->unread_count ?? 0) > 0)
                                        <span style="font-size: 0.72rem; background: var(--accent-danger); color: white; border-radius: 999px; padding: 0.12rem 0.45rem;">{{ (int) $contact->unread_count }}</span>
                                    @endif
                                </div>
                                <p style="margin: 0 0 0.2rem; color: var(--neutral-600); font-size: 0.8rem; text-transform: capitalize;">{{ $contact->role }}</p>
                                <p style="margin: 0; color: var(--neutral-700); font-size: 0.82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $contact->last_message }}</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <p style="margin: 0; color: var(--neutral-600);">No contacts available yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card messages-thread-panel" style="grid-column: 2 / -1; display: flex; flex-direction: column; min-height: 560px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;">
                @if ($selectedContact)
                    @php($selectedContactName = \App\Support\NameFormatter::title((string) $selectedContact->full_name))
                    <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 0;">
                        @if (($selectedContact->profile_image_path ?? '') !== '')
                            <img src="/{{ $selectedContact->profile_image_path }}" alt="{{ $selectedContactName }}" style="width: 42px; height: 42px; border-radius: 999px; object-fit: cover; border: 1px solid var(--neutral-200); flex-shrink: 0;">
                        @else
                            <span style="width: 42px; height: 42px; border-radius: 999px; background: var(--neutral-100); border: 1px solid var(--neutral-200); display: inline-flex; align-items: center; justify-content: center; color: var(--neutral-700); font-weight: 700; font-size: 0.9rem; flex-shrink: 0;">{{ strtoupper(substr($selectedContactName, 0, 1)) }}</span>
                        @endif
                        <div style="min-width: 0;">
                            <h3 style="margin: 0; font-size: 1.05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $selectedContactName }}</h3>
                            <p style="margin: 0; font-size: 0.8rem; color: var(--neutral-600); text-transform: capitalize;">{{ $selectedContact->role }}</p>
                        </div>
                    </div>
                @else
                    <h3 style="margin: 0; font-size: 1.05rem;">Select a conversation</h3>
                @endif
            </div>

            <div class="messages-chat-window" style="padding: 1rem; flex: 1; overflow-y: auto; border-bottom: 1px solid var(--neutral-200);">
                @if (!$selectedContact)
                    <p style="margin: 0; color: var(--neutral-600);">Choose a contact from the left or start a new chat.</p>
                @elseif (($threadMessages ?? collect())->count() === 0)
                    <p style="margin: 0; color: var(--neutral-600);">No messages yet. Send a message to start the conversation.</p>
                @else
                    @foreach ($threadMessages as $message)
                        @php($isMine = (int) $message->sender_id === (int) session('legacy_user_id'))
                        <div style="display: flex; justify-content: {{ $isMine ? 'flex-end' : 'flex-start' }}; margin-bottom: 0.55rem;">
                            <div class="{{ $isMine ? 'messages-bubble messages-bubble-user' : 'messages-bubble messages-bubble-other' }}" style="max-width: min(76%, 540px); color: {{ $isMine ? 'white' : 'var(--neutral-800)' }}; border-radius: 14px; padding: 0.6rem 0.75rem; box-shadow: var(--shadow-xs);">
                                <p style="margin: 0 0 0.35rem; color: inherit;">{{ $message->content }}</p>
                                <p style="margin: 0; font-size: 0.75rem; color: {{ $isMine ? 'rgba(255, 255, 255, 0.85)' : 'var(--neutral-500)' }}; text-align: right;">{{ \Carbon\Carbon::parse($message->created_at)->format('M d, H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="messages-chat-compose">
                @if ($selectedContact)
                    <form method="POST" action="/messages.php" class="messages-chat-compose-form">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ (int) $selectedContact->id }}">
                        <input type="hidden" name="material_id" value="{{ (int) ($materialId ?? 0) }}">
                        <textarea name="content" rows="3" maxlength="3000" required placeholder="Type your message..." style="margin: 0;">{{ (int) ($materialId ?? 0) > 0 && ($threadMessages ?? collect())->count() === 0 ? 'Hi, I am interested in material #' . (int) $materialId . '. Please share availability and delivery details.' : '' }}</textarea>
                        <div class="messages-compose-footer">
                            <span class="text-note">Maximum 3000 characters.</span>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </div>
                    </form>
                @else
                    <p style="margin: 0; color: var(--neutral-600);">Select a contact to start chatting.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
