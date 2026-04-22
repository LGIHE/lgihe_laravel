@extends('layouts.auth')

@section('title', 'Set Your Password')

@section('content')
<div class="logo">
    <h1>{{ config('app.name') }}</h1>
    <p>Set Your Password</p>
</div>

<div class="text-center">
    <h2>Welcome, {{ $user->name }}!</h2>
    <p class="subtitle">Create a secure password to access your account</p>
</div>

@if ($errors->any())
    <div class="error-message">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('password.setup.submit') }}">
    @csrf
    
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">
    
    <div class="form-group">
        <label for="email">Email Address</label>
        <input 
            type="email" 
            id="email" 
            value="{{ $email }}" 
            disabled
        >
    </div>
    
    <div class="password-requirements">
        <h4>Password Requirements:</h4>
        <ul>
            <li>At least 8 characters long</li>
            <li>Mix of letters and numbers recommended</li>
            <li>Avoid common passwords</li>
        </ul>
    </div>
    
    <div class="form-group">
        <label for="password">Password</label>
        <input 
            type="password" 
            id="password" 
            name="password" 
            required 
            minlength="8"
            autofocus
            placeholder="Enter your password"
        >
    </div>
    
    <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input 
            type="password" 
            id="password_confirmation" 
            name="password_confirmation" 
            required 
            minlength="8"
            placeholder="Confirm your password"
        >
    </div>
    
    <button type="submit" class="btn btn-primary">
        Set Password & Login
    </button>
</form>

<p class="text-center text-muted">
    Need help? <a href="mailto:{{ config('mail.from.address') }}" class="link">Contact Support</a>
</p>
@endsection

@push('scripts')
<script>
    // Simple client-side validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        
        if (password !== confirmation) {
            e.preventDefault();
            alert('Passwords do not match. Please try again.');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long.');
            return false;
        }
    });
</script>
@endpush
