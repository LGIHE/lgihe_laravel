@extends('layouts.auth')

@section('title', 'Password Set Successfully')

@section('content')
<div class="text-center">
    <div class="success-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>
    
    <h2>Password Set Successfully!</h2>
    <p class="subtitle">Welcome to {{ config('app.name') }}, {{ $user->name }}</p>
    
    <p style="color: #6b7280; margin-bottom: 32px;">
        Your password has been set and you are now logged in. You can now access the admin panel.
    </p>
    
    <a href="/admin" class="btn btn-primary" style="display: inline-block; text-decoration: none;">
        Go to Admin Panel
    </a>
    
    <p class="text-muted" style="margin-top: 24px;">
        You can close this window and use your credentials to log in anytime.
    </p>
</div>
@endsection
