@php
    $pageTitle = 'Terms & Agreement';
@endphp
@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 920px; margin: 2.5rem auto 3rem;">
    <div class="card">
        <div class="card-body" style="padding: 1.5rem;">
            <h1 style="margin-top: 0; font-size: 2rem;">Terms & Agreement</h1>
            <p class="text-muted" style="margin-bottom: 1rem;">
                Please read this agreement carefully before creating an account on NaijaBuilders.
            </p>

            <div id="termsScrollBox" style="border: 1px solid var(--neutral-200); border-radius: var(--rounded-lg); background: var(--neutral-50); height: 62vh; overflow-y: auto; padding: 1rem;">
                <pre style="margin: 0; white-space: pre-wrap; word-break: break-word; font-family: var(--font-family-base); color: var(--neutral-700); line-height: 1.75;">{{ $termsContent }}</pre>
            </div>

            <div id="termsReadHint" class="alert alert-info" data-no-auto-fade="true" style="margin-top: 1rem; margin-bottom: 0;">
                Scroll to the end of this agreement to unlock the Accept button.
            </div>

            <div style="margin-top: 1.25rem; display: flex; justify-content: flex-end; gap: 0.6rem;">
                <a href="/signup.php" class="btn btn-outline" style="text-decoration: none;">Cancel</a>
                <form method="POST" action="/terms-and-agreement/accept.php" style="margin: 0;">
                    @csrf
                    <button id="acceptTermsButton" type="submit" class="btn btn-primary" disabled aria-disabled="true" style="opacity: 0.65; cursor: not-allowed;">Accept & Continue</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var scrollBox = document.getElementById('termsScrollBox');
    var acceptButton = document.getElementById('acceptTermsButton');
    var hintBox = document.getElementById('termsReadHint');

    if (!scrollBox || !acceptButton || !hintBox) {
        return;
    }

    var unlocked = false;
    var unlockButton = function () {
        if (unlocked) {
            return;
        }
        unlocked = true;
        acceptButton.disabled = false;
        acceptButton.setAttribute('aria-disabled', 'false');
        acceptButton.style.opacity = '1';
        acceptButton.style.cursor = 'pointer';
        hintBox.className = 'alert alert-success';
        hintBox.textContent = 'Great. You reached the end. You can now accept and continue to sign up.';
    };

    var checkScrolledToEnd = function () {
        var threshold = 8;
        var distanceFromBottom = scrollBox.scrollHeight - scrollBox.scrollTop - scrollBox.clientHeight;
        if (distanceFromBottom <= threshold) {
            unlockButton();
        }
    };

    scrollBox.addEventListener('scroll', checkScrolledToEnd);
    window.addEventListener('load', checkScrolledToEnd);
})();
</script>
@endpush
