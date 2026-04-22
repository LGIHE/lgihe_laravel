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
        Your password has been set and you are now logged in.
    </p>
    
    @if($canAccessPanel)
        <a href="/admin" class="btn btn-primary" style="display: inline-block; text-decoration: none;">
            Go to Admin Panel
        </a>
        
        <p class="text-muted" style="margin-top: 24px;">
            You can close this window and use your credentials to log in anytime.
        </p>
    @else
        <div style="background-color: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; padding: 20px; margin-bottom: 24px; text-align: left;">
            <h4 style="font-size: 14px; font-weight: 600; color: #92400e; margin-bottom: 12px;">⚠️ Access Pending</h4>
            <p style="font-size: 14px; color: #92400e; margin: 0;">
                Your account has been created successfully, but you don't have the necessary roles or permissions to access the admin panel yet. 
                Please contact your administrator to assign you the appropriate role.
            </p>
        </div>
        
        <a href="mailto:{{ config('mail.from.address') }}" class="btn btn-primary" style="display: inline-block; text-decoration: none;">
            Contact Administrator
        </a>
        
        <p class="text-muted" style="margin-top: 24px;">
            Once your administrator assigns you a role, you'll be able to access the admin panel at <a href="/admin" class="link">/admin</a>
        </p>
    @endif
</div>
@endsection
