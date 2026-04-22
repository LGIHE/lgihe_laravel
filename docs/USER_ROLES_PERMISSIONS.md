# User Roles and Permissions Guide

## Overview

Users must have at least one role assigned to access the Filament admin panel. Without a role, users will see a 403 Forbidden error when trying to access `/admin`.

## Available Roles

Your system has the following roles:

1. **Super Admin** - Full system access
2. **Admin** - Administrative access
3. **Editor** - Content management access
4. **Viewer** - Read-only access

## Creating Users with Roles

### Via Admin Panel

When creating a user in the admin panel:

1. Go to **Users** → **New User**
2. Fill in user details (name, email)
3. **Important:** Select at least one role in the "Role & Permissions" section
4. Leave password empty to send password setup email
5. Click **Create**

**⚠️ Warning:** If you don't assign a role, the user will be able to set their password but won't be able to access the admin panel.

### What Happens After User Creation

#### With Role Assigned ✅
1. User receives password setup email
2. User sets password
3. User is logged in automatically
4. User can access admin panel
5. Success page shows "Go to Admin Panel" button

#### Without Role Assigned ⚠️
1. User receives password setup email
2. User sets password
3. User is logged in automatically
4. User **cannot** access admin panel (403 error)
5. Success page shows warning message with instructions

## Fixing 403 Errors

If a user gets a 403 error when accessing `/admin`:

### Solution 1: Assign Role via Admin Panel

1. Log in as Super Admin or Admin
2. Go to **Users**
3. Find the user and click **Edit**
4. Scroll to "Role & Permissions" section
5. Select at least one role (e.g., "Viewer" for basic access)
6. Click **Save**
7. User can now access the admin panel

### Solution 2: Assign Role via Tinker

```bash
php artisan tinker
```

```php
// Find the user
$user = \App\Models\User::where('email', 'user@example.com')->first();

// Assign a role
$user->assignRole('Viewer');

// Or assign multiple roles
$user->assignRole(['Editor', 'Viewer']);

// Verify
$user->roles->pluck('name');
```

### Solution 3: Assign Role via Database

```bash
php artisan tinker
```

```php
// Get user and role
$user = \App\Models\User::find(1);
$role = \Spatie\Permission\Models\Role::where('name', 'Viewer')->first();

// Assign role
$user->roles()->attach($role->id);
```

## Understanding Panel Access

The `canAccessPanel()` method in the User model determines access:

```php
public function canAccessPanel(Panel $panel): bool
{
    try {
        return $this->hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Viewer']) || 
               $this->hasPermissionTo('view_users');
    } catch (\Exception $e) {
        return true; // Fallback for tests
    }
}
```

**Access is granted if:**
- User has any of the listed roles, OR
- User has the `view_users` permission

## Role Permissions

Each role has different permissions:

### Super Admin
- Full access to everything
- Can manage users, roles, and permissions
- Can access all resources

### Admin
- Administrative access
- Can manage most resources
- Can manage users (except Super Admins)

### Editor
- Content management
- Can create/edit news, events, jobs
- Cannot manage users or settings

### Viewer
- Read-only access
- Can view resources
- Cannot create, edit, or delete

## Best Practices

### When Creating Users

1. ✅ **Always assign at least one role**
2. ✅ Use "Viewer" for basic access
3. ✅ Use "Editor" for content managers
4. ✅ Use "Admin" for administrators
5. ✅ Use "Super Admin" sparingly (only for system admins)

### Default Role Recommendation

Consider setting a default role for new users. You can modify the `CreateUser` page:

```php
protected function afterCreate(): void
{
    // Assign default role if none assigned
    if ($this->record->roles()->count() === 0) {
        $this->record->assignRole('Viewer');
    }
    
    // Send email...
}
```

## Checking User Roles

### Via Tinker

```bash
php artisan tinker
```

```php
// Check user's roles
$user = \App\Models\User::find(1);
$user->roles->pluck('name');

// Check if user has specific role
$user->hasRole('Admin');

// Check if user has any role
$user->hasAnyRole(['Admin', 'Editor']);

// Check user's permissions
$user->permissions->pluck('name');

// Check if user can access panel
$user->canAccessPanel(\Filament\Facades\Filament::getCurrentPanel());
```

### Via Code

```php
// In a controller or model
if ($user->hasRole('Admin')) {
    // User is an admin
}

if ($user->canAccessPanel($panel)) {
    // User can access the panel
}
```

## Creating New Roles

If you need to create additional roles:

```bash
php artisan tinker
```

```php
use Spatie\Permission\Models\Role;

// Create new role
$role = Role::create(['name' => 'Content Manager']);

// Assign permissions to role
$role->givePermissionTo(['view_news', 'create_news', 'update_news']);
```

## Troubleshooting

### User Can't Access Panel After Password Setup

**Problem:** User sets password successfully but gets 403 error.

**Solution:**
1. Check if user has any roles assigned
2. Assign at least one role via admin panel
3. User should now be able to access `/admin`

### User Has Role But Still Gets 403

**Problem:** User has a role but still can't access panel.

**Possible causes:**
1. Cache issue - Clear cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

2. Role name mismatch - Check exact role name:
   ```bash
   php artisan tinker --execute="echo \Spatie\Permission\Models\Role::pluck('name');"
   ```

3. Permission issue - Check `canAccessPanel()` method in User model

### How to Make All Authenticated Users Access Panel

If you want to allow all authenticated users (regardless of roles):

Edit `app/Models/User.php`:

```php
public function canAccessPanel(Panel $panel): bool
{
    // Allow all authenticated users
    return true;
}
```

**⚠️ Warning:** This removes role-based access control. Not recommended for production.

## Summary

- ✅ **Always assign roles** when creating users
- ✅ Use "Viewer" as minimum role for panel access
- ✅ Users without roles get 403 error
- ✅ Assign roles via admin panel or Tinker
- ✅ Success page now shows appropriate message based on access
- ✅ Admin panel shows warning if user created without roles

## Quick Commands

```bash
# Check user's roles
php artisan tinker --execute="\$u = \App\Models\User::find(1); echo \$u->roles->pluck('name');"

# Assign role to user
php artisan tinker --execute="\$u = \App\Models\User::find(1); \$u->assignRole('Viewer');"

# List all roles
php artisan tinker --execute="echo \Spatie\Permission\Models\Role::pluck('name');"

# List all users without roles
php artisan tinker --execute="\App\Models\User::doesntHave('roles')->get(['id', 'name', 'email'])->each(fn(\$u) => print(\$u->id . ': ' . \$u->email . PHP_EOL));"
```
