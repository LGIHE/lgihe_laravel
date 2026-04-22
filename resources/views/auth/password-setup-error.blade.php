@extends('layouts.auth')

@section('title', 'Invalid Link')

@section('content')
<div class="text-center">
    <div class="error-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </div>
    
    <h2>Invalid or Expired Link</h2>
    <p class="subtitle">We couldn't verify your password setup link</p>
    
    <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin-bottom: 32px; text-align: left;">
        <p style="color: #dc2626; font-size: 14px; margin: 0;">
            {{ $error }}
        </p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 24px; text-align: left;">
        <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px;">What to do next:</h4>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="font-size: 14px; color: #6b7280; padding-left: 24px; position: relative; margin-bottom: 8px;">
                <span style="position: absolute; left: 0; color: #667eea;">1.</span>
                Contact your administrator to resend the password setup email
            </li>
            <li style="font-size: 14px; color: #6b7280; padding-left: 24px; position: relative; margin-bottom: 8px;">
                <span style="position: absolute; left: 0; color: #667eea;">2.</span>
                Make sure you're using the latest link from your email
            </li>
            <li style="font-size: 14px; color: #6b7280; padding-left: 24px; position: relative;">
                <span style="position: absolute; left: 0; color: #667eea;">3.</span>
                Links expire after 60 minutes for security
            </li>
        </ul>
    </div>
    
    <a href="mailto:{{ config('mail.from.address') }}" class="btn btn-primary" style="display: inline-block; text-decoration: none;">
        Contact Support
    </a>
    
    <p class="text-muted" style="margin-top: 24px;">
        <a href="/" class="link">Return to Homepage</a>
    </p>
</div>
@endsection
