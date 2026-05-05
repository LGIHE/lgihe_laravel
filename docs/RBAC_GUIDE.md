# 🔐 Role-Based Access Control (RBAC) Guide

Complete guide for managing users, roles, and permissions in the LGIHE system.

## 🚀 Quick Start

```bash
# Setup RBAC system
php artisan migrate
php artisan db:seed --class=PermissionSeeder

# Create super admin
php artisan make:super-admin

# Clear permission cache (after changes)
php artisan permission:cache-reset
```

## 📊 System Overview

### Default Roles

| Role | Permissions | Use Case |
|------|-------------|----------|
| **Super Admin** | All (29) | System administrators |
| **Admin** | Content only (21) | Department heads |
| **Editor** | Create/Edit (14) | Content creators |
| **Viewer** | View only (7) | Auditors, observers |

### Permission Categories

- **User Management** (8 permissions): view, create, update, delete users
- **Role Management** (4 permissions): view, create, update, delete roles
- **Content Management** (16 permissions): news, events, jobs, tenders
- **Applications** (4 permissions): view, update application status
- **Contact Inquiries** (4 permissions): view, update, delete inquiries
- **Analytics** (1 permission): view analytics dashboard

**Total: 29 permissions**

## 👥 Managing Roles

### Create a Custom Role

1. Navigate to **User Management > Roles**
2. Click **New Role**
3. Enter role name and description
4. Select permissions by category
5. Click **Create**

### Edit Role Permissions

1. Go to **User Management > Roles**
2. Click on the role to edit
3. Modify permissions as needed
4. Click **Save**

### Delete a Role

⚠️ **Warning**: Cannot delete roles with assigned users

1. Reassign all users to different roles
2. Go to **User Management > Roles**
3. Click delete on the role
4. Confirm deletion

## 👤 Managing Users

### Create New User

1. Navigate to **User Management > Users**
2. Click **New User**
3. Fill in user details (name, email, password)
4. Select role(s) from dropdown
5. Optionally add direct permissions
6. Click **Create**

### Assign Roles to User

1. Edit the user
2. Select one or more roles from the dropdown
3. Click **Save**

### Grant Additional Permissions

Users can have permissions beyond their role:

1. Edit the user
2. Scroll to **Additional Permissions**
3. Check specific permissions
4. Click **Save**

## 💻 Developer Guide

### Check Permissions in Code

```php
// Check single permission
if (auth()->user()->can('view_news')) {
    // User has permission
}

// Check multiple permissions (any)
if (auth()->user()->canAny(['create_news', 'update_news'])) {
    // User has at least one permission
}

// Check role
if (auth()->user()->hasRole('Admin')) {
    // User is admin
}

// Check multiple roles
if (auth()->user()->hasAnyRole(['Admin', 'Super Admin'])) {
    // User has at least one role
}
```

### Protect Filament Resources

```php
use Filament\Resources\Resource;

class NewsResource extends Resource
{
    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_news');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_news');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update_news');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete_news');
    }
}
```

### Protect API Routes

```php
// In routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/news', [NewsController::class, 'index'])
        ->middleware('permission:view_news');
    
    Route::post('/admin/news', [NewsController::class, 'store'])
        ->middleware('permission:create_news');
});
```

### Add New Permissions

1. Add to `PermissionSeeder.php`:

```php
$permissions = [
    // ... existing permissions
    'view_new_resource',
    'create_new_resource',
    'update_new_resource',
    'delete_new_resource',
];
```

2. Run seeder:

```bash
php artisan db:seed --class=PermissionSeeder
```

3. Assign to roles as needed

## 🧪 Testing

```bash
# Run RBAC tests
php artisan test --filter=RBACTest

# Test specific permission
php artisan tinker
>>> $user = User::find(1);
>>> $user->can('view_news');
```

## 🔧 Troubleshooting

### Can't Access Admin Panel

**Problem**: User can't login or see any resources

**Solution**:
```bash
# Assign a role to the user
php artisan tinker
>>> $user = User::where('email', 'user@example.com')->first();
>>> $user->assignRole('Editor');
```

### Permissions Not Working

**Problem**: User has permission but still can't access resource

**Solution**:
```bash
# Clear permission cache
php artisan permission:cache-reset

# Clear all caches
php artisan optimize:clear
```

### Can't Delete Role

**Problem**: Error when trying to delete role

**Solution**: Reassign all users first
```bash
php artisan tinker
>>> $role = Role::where('name', 'OldRole')->first();
>>> $users = $role->users;
>>> foreach($users as $user) {
>>>     $user->assignRole('NewRole');
>>>     $user->removeRole('OldRole');
>>> }
>>> $role->delete();
```

### New Permissions Missing

**Problem**: Added new permissions but they don't appear

**Solution**:
```bash
# Re-run permission seeder
php artisan db:seed --class=PermissionSeeder

# Clear cache
php artisan permission:cache-reset
```

## 📋 Best Practices

### ✅ DO
- Use roles for common permission sets
- Follow principle of least privilege
- Regularly audit user permissions
- Use descriptive role names
- Check permissions, not roles in code
- Clear cache after permission changes

### ❌ DON'T
- Give everyone Super Admin access
- Check roles instead of permissions in code
- Delete roles with assigned users
- Grant unnecessary permissions
- Skip cache clearing after changes
- Hardcode role names in business logic

## 🔐 Security Considerations

1. **Principle of Least Privilege**: Give users only the permissions they need
2. **Regular Audits**: Review user permissions quarterly
3. **Super Admin Access**: Limit to 2-3 trusted administrators
4. **Password Policies**: Enforce strong passwords for admin users
5. **Activity Logging**: Monitor permission changes and access patterns

## 📚 Additional Resources

- [Spatie Laravel Permission Docs](https://spatie.be/docs/laravel-permission)
- [Filament Authorization Docs](https://filamentphp.com/docs/3.x/panels/users#authorization)
- [Laravel Authorization Docs](https://laravel.com/docs/authorization)

---

**Version**: 1.0.0  
**Last Updated**: May 5, 2026  
**Status**: Production Ready ✅
