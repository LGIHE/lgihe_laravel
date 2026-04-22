# Profile API Documentation

This document describes the profile management endpoints for authenticated users.

## Authentication

All profile endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:

```
Authorization: Bearer {your-token}
```

## Endpoints

### Get Profile

Get the authenticated user's profile information.

**Endpoint:** `GET /api/v1/profile`

**Response:**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": "2024-01-01T00:00:00.000000Z",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "roles": ["Admin"],
        "permissions": ["view_users", "create_users"]
    }
}
```

### Update Profile

Update the authenticated user's profile information.

**Endpoint:** `PUT /api/v1/profile`

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com"
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `email`: required, valid email, unique (excluding current user), max 255 characters

**Success Response:**
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": "2024-01-01T00:00:00.000000Z",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "roles": ["Admin"],
        "permissions": ["view_users", "create_users"]
    }
}
```

**Error Response (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

### Change Password

Change the authenticated user's password.

**Endpoint:** `POST /api/v1/profile/change-password`

**Request Body:**
```json
{
    "current_password": "oldpassword123",
    "password": "newpassword123",
    "password_confirmation": "newpassword123",
    "revoke_all_tokens": false
}
```

**Validation Rules:**
- `current_password`: required, string
- `password`: required, confirmed, must meet Laravel's default password rules
- `revoke_all_tokens`: optional, boolean (default: false)

**Success Response:**
```json
{
    "success": true,
    "message": "Password changed successfully"
}
```

**Success Response (with token revocation):**
```json
{
    "success": true,
    "message": "Password changed successfully. All sessions have been logged out."
}
```

**Error Response (422 - Wrong Current Password):**
```json
{
    "success": false,
    "message": "Current password is incorrect",
    "errors": {
        "current_password": ["The current password is incorrect."]
    }
}
```

**Error Response (422 - Validation Failed):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "password": ["The password confirmation does not match."]
    }
}
```

### Delete Account

Delete the authenticated user's account. This action is irreversible.

**Endpoint:** `DELETE /api/v1/profile`

**Request Body:**
```json
{
    "password": "userpassword123"
}
```

**Validation Rules:**
- `password`: required, string (must match current password)

**Success Response:**
```json
{
    "success": true,
    "message": "Account deleted successfully"
}
```

**Error Response (422 - Wrong Password):**
```json
{
    "success": false,
    "message": "Password is incorrect",
    "errors": {
        "password": ["The password is incorrect."]
    }
}
```

## Security Features

1. **Password Verification**: All sensitive operations (password change, account deletion) require current password verification.

2. **Token Management**: When changing password, users can optionally revoke all existing tokens to force re-login on all devices.

3. **Input Validation**: All inputs are validated using Laravel's validation system with appropriate rules.

4. **Email Uniqueness**: Email addresses must be unique across all users (excluding the current user when updating).

5. **Password Requirements**: New passwords must meet Laravel's default password requirements (configurable in `config/auth.php`).

## Usage Examples

### JavaScript/Fetch Example

```javascript
// Get profile
const profile = await fetch('/api/v1/profile', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

// Update profile
const updateProfile = await fetch('/api/v1/profile', {
    method: 'PUT',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        name: 'New Name',
        email: 'newemail@example.com'
    })
});

// Change password
const changePassword = await fetch('/api/v1/profile/change-password', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        current_password: 'oldpassword',
        password: 'newpassword',
        password_confirmation: 'newpassword',
        revoke_all_tokens: true
    })
});
```

### cURL Examples

```bash
# Get profile
curl -X GET /api/v1/profile \
  -H "Authorization: Bearer your-token" \
  -H "Accept: application/json"

# Update profile
curl -X PUT /api/v1/profile \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"New Name","email":"new@example.com"}'

# Change password
curl -X POST /api/v1/profile/change-password \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"current_password":"old","password":"new","password_confirmation":"new"}'

# Delete account
curl -X DELETE /api/v1/profile \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"password":"userpassword"}'
```