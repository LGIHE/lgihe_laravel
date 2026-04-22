# Resend.com Email Integration

This document describes the Resend.com email integration for user creation and password setup.

## Overview

When a new user is created in the admin panel, they automatically receive an email with a secure link to set their password. The password setup page is hosted on this backend application (not the frontend). After setting their password, users are automatically logged in and can access the admin panel.

## Setup Instructions

### 1. Install Dependencies

The Resend PHP package has already been installed. If needed, run:

```bash
composer install
```

### 2. Configure Resend API Key

1. Sign up for a Resend account at [resend.com](https://resend.com)
2. Create an API key in your Resend dashboard
3. Add the API key to your `.env` file:

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_your_api_key_here
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Configure Application URL

Set your backend application URL in the `.env` file:

```env
APP_URL=http://localhost:8000
```

In production, this should be your actual domain (e.g., `https://api.lgihe.ac.ug`).

### 4. Verify Domain in Resend

In your Resend dashboard, verify the domain you're sending emails from (e.g., `lgihe.org`). This ensures your emails are delivered successfully.

## How It Works

### User Creation Flow

1. **Admin creates a user** in the Filament admin panel
2. **Password is optional** - if left empty, a random password is generated
3. **Email is sent** via Resend with a password setup link
4. **User clicks link** and is taken to the backend password setup page
5. **User sets password** on the backend
6. **User is automatically logged in** and redirected to admin panel

### Password Setup URL

The email contains a link like:
```
http://localhost:8000/set-password?token=abc123xyz&email=user@example.com
```

This link points to a route on the backend application, not the frontend.

## Routes

### Web Routes (Backend UI)

#### Show Password Setup Form
```
GET /set-password?token={token}&email={email}
```

Displays the password setup form where users can create their password.

#### Submit Password Setup
```
POST /set-password
```

Processes the password setup form submission.

#### Success Page
```
GET /password-setup-success
```

Shows success message after password is set (requires authentication).

### API Routes (Optional - for frontend integration if needed later)

#### Verify Token
```
POST /api/v1/password/verify-token
```

Request:
```json
{
  "token": "reset_token_here",
  "email": "user@example.com"
}
```

Response:
```json
{
  "message": "Token is valid.",
  "user": {
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

#### Setup Password
```
POST /api/v1/password/setup
```

Request:
```json
{
  "token": "reset_token_here",
  "email": "user@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

Response:
```json
{
  "message": "Password has been set successfully.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "email_verified_at": "2026-04-22T10:30:00.000000Z"
  },
  "token": "sanctum_auth_token_here"
}
```

## Email Template

The email sent to users includes:

- Welcome greeting with user's name
- Explanation that their account has been created
- Call-to-action button to set password
- Expiration notice (default: 60 minutes)
- Support contact information

## Security Features

- **Secure tokens**: Uses Laravel's built-in password reset token system
- **Token expiration**: Tokens expire after 60 minutes (configurable in `config/auth.php`)
- **Email verification**: Email is automatically verified when password is set
- **Auto-login**: User is logged in via Laravel's session after password setup
- **Password validation**: Enforces Laravel's password rules
- **Single-use tokens**: Tokens can only be used once

## Testing

### Local Testing

For local development, you can use the `log` mailer to see emails in your logs:

```env
MAIL_MAILER=log
```

Emails will be written to `storage/logs/laravel.log`.

### Queue Configuration

The notification is queued by default for better performance. Make sure your queue worker is running:

```bash
php artisan queue:work
```

For local development, you can use the `sync` queue driver:

```env
QUEUE_CONNECTION=sync
```

### Testing the Flow

1. Set `MAIL_MAILER=log` in `.env`
2. Create a test user in the admin panel (leave password empty)
3. Check `storage/logs/laravel.log` for the email
4. Copy the password setup URL from the log
5. Open the URL in your browser
6. Set a password
7. Verify you're redirected to the success page
8. Click "Go to Admin Panel" to access the admin

## Troubleshooting

### Emails not sending

1. Check your Resend API key is correct
2. Verify your domain in Resend dashboard
3. Check the `MAIL_FROM_ADDRESS` matches your verified domain
4. Review logs in `storage/logs/laravel.log`

### Token expired errors

- Tokens expire after 60 minutes by default
- Users need to request a new password setup email
- Admins can resend the email by editing and saving the user

### "Invalid Link" error

- Token may have expired
- Token may have already been used
- Email parameter may be incorrect
- User may not exist in database

## Configuration Options

### Token Expiration

Edit `config/auth.php` to change token expiration:

```php
'passwords' => [
    'users' => [
        'expire' => 60, // minutes
    ],
],
```

### Email Customization

Edit `app/Notifications/UserCreatedNotification.php` to customize the email content, subject, or styling.

### Password Requirements

Edit `config/auth.php` or modify the validation rules in the controller to change password requirements.

## Views

The password setup UI uses Blade templates located in:

- `resources/views/layouts/auth.blade.php` - Base layout
- `resources/views/auth/password-setup.blade.php` - Password setup form
- `resources/views/auth/password-setup-success.blade.php` - Success page
- `resources/views/auth/password-setup-error.blade.php` - Error page

You can customize these views to match your branding.

## Support

For issues with Resend integration, contact the development team or refer to:
- [Resend Documentation](https://resend.com/docs)
- [Laravel Mail Documentation](https://laravel.com/docs/mail)
- [Laravel Authentication](https://laravel.com/docs/authentication)
