# RBAC Quick Reference Card

## 🚀 Initial Setup

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder
php artisan make:super-admin
```

## 👥 Default Roles

| Role | Permissions | Use Case |
|------|-------------|----------|
| **Super Admin** | All (29) | System administrators |
| **Admin** | Content only (21) | Department heads |
| **Editor** | Create/Edit content (14) | Content creators |
| **Viewer** | View only (7) | Auditors, observers |

## 🔑 Permission Naming

Pattern: `{action}_{resource}`

**Actions**: `view`, `create`, `update`, `delete`

**Resources**: `users`, `roles`, `applications`, `contact_inquiries`, `events`, `job_listings`, `news`, `analytics`

## 💻 Code Examples

### Check Permission
```php
auth()->user()->can('view_news')
```

### Check Role
```php
auth()->user()->hasRole('Admin')
```

### Assign Role
```php
$user->assignRole('Editor')
```

### Give Permission
```php
$user->givePermissionTo('view_news')
```

### Resource Authorization
```php
public static function canViewAny(): bool
{
    return auth()->user()->can('view_resource_name');
}
```

## 🎯 Common Tasks

### Create New Role
1. Admin Panel → User Management → Roles
2. Click "New Role"
3. Enter name and select permissions
4. Save

### Assign Role to User
1. Admin Panel → User Management → Users
2. Edit user
3. Select role(s) from dropdown
4. Save

### Add New Resource Permissions
1. Edit `PermissionSeeder.php`
2. Add resource to `$resources` array
3. Run: `php artisan db:seed --class=PermissionSeeder`
4. Update roles with new permissions

## 🔧 Troubleshooting

| Problem | Solution |
|---------|----------|
| Can't access panel | Assign a role to user |
| Permissions not working | `php artisan permission:cache-reset` |
| Can't delete role | Reassign users first |
| New permissions missing | Re-run seeder |

## 📊 All Permissions (29)

### User Management (8)
- view_users, create_users, update_users, delete_users
- view_roles, create_roles, update_roles, delete_roles

### Content (16)
- view_events, create_events, update_events, delete_events
- view_job_listings, create_job_listings, update_job_listings, delete_job_listings
- view_news, create_news, update_news, delete_news
- view_applications, create_applications, update_applications, delete_applications

### Other (5)
- view_contact_inquiries, create_contact_inquiries, update_contact_inquiries, delete_contact_inquiries
- view_analytics

## 🧪 Test Commands

```bash
# Run all RBAC tests
php artisan test --filter=RBACTest

# Check user permissions in Tinker
php artisan tinker
>>> $user = User::find(1)
>>> $user->getAllPermissions()
>>> $user->getRoleNames()
```

## 📱 Admin Panel URLs

- Users: `/admin/users`
- Roles: `/admin/roles`
- Applications: `/admin/applications`
- Events: `/admin/events`
- News: `/admin/news`
- Job Listings: `/admin/job-listings`
- Contact Inquiries: `/admin/contact-inquiries`

## 🔐 Security Best Practices

1. ✅ Use permissions, not roles in code
2. ✅ Follow principle of least privilege
3. ✅ Limit Super Admin access
4. ✅ Regular permission audits
5. ✅ Clear permission cache after changes

## 📚 Documentation Files

- **RBAC_SETUP.md** - User guide
- **RBAC_DEVELOPER_GUIDE.md** - Developer guide
- **RBAC_IMPLEMENTATION_SUMMARY.md** - Implementation details
- **RBAC_QUICK_REFERENCE.md** - This file

## 🆘 Need Help?

1. Check documentation files
2. Review test cases in `tests/Feature/RBACTest.php`
3. Consult [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
4. Contact system administrator
