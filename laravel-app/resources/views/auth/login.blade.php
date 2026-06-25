@php
    $pageTitle = 'Login';
    $message = match ($errorCode ?? '') {
        'missing_credentials' => 'Please provide your email or username and password.',
        'invalid_credentials' => 'Invalid login or password.',
        'db_unavailable' => 'Login is temporarily unavailable because the database is offline. Please start MySQL and try again.',
        default => '',
    };
@endphp
@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 500px; margin: 4rem auto;">
    <div class="card"><div class="card-body">
        <h2 class="text-center" style="margin-top: 0;">Welcome Back</h2>
        <p class="text-center text-muted">Sign in to your NaijaBuilders account</p>

        @if ($message !== '')
            <div class="alert alert-danger">{{ $message }}</div>
        @endif

        <form method="POST" action="/auth/login.php">
            @csrf
            <div class="form-group">
                <label for="login">Email or Username</label>
                <input type="text" id="login" name="login" autocomplete="username" required placeholder="you@example.com or your_username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            <div class="flex-between" style="margin-bottom: 1.5rem;">
                <label style="font-weight: normal;"><input type="checkbox" name="remember_me"> Remember me</label>
                <a href="/forgot-password.php" style="color: var(--primary-color);">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom: 1rem;">Sign In</button>
        </form>

        <div style="text-align: center; border-top: 1px solid var(--neutral-200); padding-top: 1.5rem;">
            <p style="margin-bottom: 0;">Don't have an account? <a href="/signup.php" style="font-weight: bold;">Create one</a></p>
        </div>
    </div></div>
</div>
@endsection
