@php
    $pageTitle = 'Support Center';
@endphp
@extends('layouts.app')

@section('content')
<div class="container support-page" style="padding-top: 2rem; padding-bottom: 3rem;">
    <section class="support-page__hero" style="position: relative; overflow: hidden; border-radius: var(--rounded-lg); margin-bottom: 1.5rem; background: radial-gradient(circle at 85% 15%, rgba(255, 255, 255, 0.22), transparent 42%), linear-gradient(140deg, var(--primary-color), var(--secondary-color)); color: white; padding: 2rem; box-shadow: 0 18px 38px rgba(11, 42, 91, 0.2);">
        <div style="max-width: 820px; position: relative; z-index: 1;">
            <p style="margin: 0 0 0.55rem; font-size: 0.82rem; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255, 255, 255, 0.85);">NaijaBuilders Support</p>
            <h1 style="margin: 0 0 0.65rem; color: white; font-size: clamp(1.55rem, 4vw, 2.2rem);">Get instant help from Support AI</h1>
            <p style="margin: 0; max-width: 62ch; color: rgba(255, 255, 255, 0.92); line-height: 1.7;">Ask questions about your account, listings, orders, messages, and subscription plans. For advanced issues, use the quick support links below.</p>
        </div>
    </section>

    <section class="support-page__layout" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; align-items: start;">
        <article class="card support-page__chat-card" style="border: 1px solid var(--neutral-200); box-shadow: 0 10px 28px rgba(14, 28, 51, 0.08); overflow: hidden;">
            <div class="card-header" style="display: flex; justify-content: space-between; gap: 0.75rem; align-items: center;">
                <h3 style="margin: 0;">Support AI Chat</h3>
                <span style="font-size: 0.8rem; color: var(--secondary-color); background: var(--secondary-light); border-radius: 999px; padding: 0.2rem 0.6rem;">Basic Support</span>
            </div>

            <div id="supportChatWindow" class="support-chat-window" style="padding: 1rem; height: 420px; overflow-y: auto; border-top: 1px solid var(--neutral-200); border-bottom: 1px solid var(--neutral-200);">
                <div style="display: grid; gap: 0.6rem;">
                    <div class="support-bubble support-bubble-bot" style="max-width: 88%; border-radius: 12px; padding: 0.65rem 0.75rem;">
                        Hello. I am Support AI. I can help with login issues, plan questions, listings, orders, and messaging.
                    </div>
                    <div class="support-bubble support-bubble-bot" style="max-width: 88%; border-radius: 12px; padding: 0.65rem 0.75rem;">
                        Try questions like: "How do I upgrade plan?" or "Why is my listing not visible?"
                    </div>
                </div>
            </div>

            <div style="padding: 0.85rem 1rem 1rem;">
                <div style="display: flex; flex-wrap: wrap; gap: 0.45rem; margin-bottom: 0.7rem;">
                    <button type="button" class="btn btn-outline btn-sm js-support-prompt" data-prompt="How do I upgrade my subscription plan?">Plan upgrade</button>
                    <button type="button" class="btn btn-outline btn-sm js-support-prompt" data-prompt="My listing is not showing to buyers">Listing visibility</button>
                    <button type="button" class="btn btn-outline btn-sm js-support-prompt" data-prompt="How can I message a supplier?">Messages</button>
                    <button type="button" class="btn btn-outline btn-sm js-support-prompt" data-prompt="How do I fix login problem?">Login issues</button>
                </div>

                <form id="supportChatForm" class="support-page__chat-form" style="display: grid; grid-template-columns: 1fr auto; gap: 0.55rem;">
                    <label for="supportChatInput" class="sr-only">Ask Support AI</label>
                    <input
                        id="supportChatInput"
                        type="text"
                        placeholder="Type your support question..."
                        autocomplete="off"
                        style="margin: 0;"
                    >
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
        </article>

        <aside class="card support-page__quick-card" style="border: 1px solid var(--neutral-200); box-shadow: 0 10px 24px rgba(14, 28, 51, 0.08);">
            <div class="card-header"><h3 style="margin: 0;">Quick Support</h3></div>
            <div class="card-body" style="display: grid; gap: 0.65rem;">
                <a href="/subscription.php" class="btn btn-outline" style="text-decoration: none; justify-content: flex-start;">Subscription & Billing</a>
                <a href="/messages.php" class="btn btn-outline" style="text-decoration: none; justify-content: flex-start;">Contact via Messages</a>
                <a href="/edit-profile.php" class="btn btn-outline" style="text-decoration: none; justify-content: flex-start;">Update Profile</a>
                <a href="/materials.php" class="btn btn-outline" style="text-decoration: none; justify-content: flex-start;">Browse Materials</a>
            </div>
        </aside>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var chatWindow = document.getElementById('supportChatWindow');
    var chatForm = document.getElementById('supportChatForm');
    var chatInput = document.getElementById('supportChatInput');
    var quickPrompts = document.querySelectorAll('.js-support-prompt');

    if (!chatWindow || !chatForm || !chatInput) {
        return;
    }

    var supportRules = [
        {
            keys: ['upgrade', 'plan', 'subscription', 'billing', 'price', 'payment'],
            reply: 'To change plan, open Subscription, choose a plan, and confirm. Pro and Enterprise activate the verified badge.'
        },
        {
            keys: ['listing', 'visible', 'showing', 'inactive', 'stock', 'supplier'],
            reply: 'Check your listing status in View Listings. Keep status as active, set stock above zero, and confirm price is entered correctly.'
        },
        {
            keys: ['login', 'password', 'sign in', 'account locked', 'cannot access'],
            reply: 'For login issues, use Forgot Password from login page, then check your email. If issue remains, update profile email or contact support through Messages.'
        },
        {
            keys: ['message', 'chat', 'inbox', 'reply', 'buyer'],
            reply: 'Open Messages to send or reply. Keep messages clear with product name, quantity, location, and timeline for faster responses.'
        },
        {
            keys: ['order', 'delivery', 'cancel', 'pending', 'processing'],
            reply: 'Order updates appear on your dashboard and messages. For delivery or status problems, contact the supplier first, then escalate through support if unresolved.'
        }
    ];

    function appendMessage(text, sender) {
        var wrapper = document.createElement('div');
        wrapper.style.display = 'flex';
        wrapper.style.justifyContent = sender === 'user' ? 'flex-end' : 'flex-start';
        wrapper.style.marginBottom = '0.55rem';

        var bubble = document.createElement('div');
        bubble.textContent = text;
        bubble.style.maxWidth = '88%';
        bubble.style.padding = '0.65rem 0.75rem';
        bubble.style.borderRadius = '12px';
        bubble.style.whiteSpace = 'pre-wrap';

        bubble.className = sender === 'user' ? 'support-bubble support-bubble-user' : 'support-bubble support-bubble-bot';

        wrapper.appendChild(bubble);
        chatWindow.appendChild(wrapper);
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function appendTypingIndicator() {
        var wrapper = document.createElement('div');
        wrapper.className = 'support-typing-wrap';
        wrapper.style.display = 'flex';
        wrapper.style.justifyContent = 'flex-start';
        wrapper.style.marginBottom = '0.55rem';

        var bubble = document.createElement('div');
        bubble.className = 'support-bubble support-bubble-bot support-typing-bubble';
        bubble.innerHTML = '<span class="support-typing-dot"></span><span class="support-typing-dot"></span><span class="support-typing-dot"></span>';

        wrapper.appendChild(bubble);
        chatWindow.appendChild(wrapper);
        chatWindow.scrollTop = chatWindow.scrollHeight;

        return wrapper;
    }

    function getSupportReply(inputText) {
        var text = inputText.toLowerCase();

        for (var i = 0; i < supportRules.length; i += 1) {
            var rule = supportRules[i];
            for (var j = 0; j < rule.keys.length; j += 1) {
                if (text.indexOf(rule.keys[j]) !== -1) {
                    return rule.reply;
                }
            }
        }

        return 'I can help with basic topics: login, listing visibility, messages, orders, and subscription plans. Please rephrase your question with one of those keywords.';
    }

    function handleUserMessage(message) {
        appendMessage(message, 'user');

        var typingIndicator = appendTypingIndicator();
        var messageLength = message.length;
        var baseDelay = 420;
        var variableDelay = Math.min(380, messageLength * 8);
        var jitter = Math.floor(Math.random() * 160);
        var replyDelay = Math.max(520, Math.min(980, baseDelay + variableDelay + jitter));

        window.setTimeout(function () {
            if (typingIndicator && typingIndicator.parentNode) {
                typingIndicator.parentNode.removeChild(typingIndicator);
            }

            appendMessage(getSupportReply(message), 'bot');
        }, replyDelay);
    }

    chatForm.addEventListener('submit', function (event) {
        event.preventDefault();
        var message = chatInput.value.trim();
        if (message === '') {
            return;
        }

        chatInput.value = '';
        handleUserMessage(message);
    });

    quickPrompts.forEach(function (promptButton) {
        promptButton.addEventListener('click', function () {
            var prompt = (promptButton.getAttribute('data-prompt') || '').trim();
            if (prompt === '') {
                return;
            }

            handleUserMessage(prompt);
        });
    });
})();
</script>
@endpush
