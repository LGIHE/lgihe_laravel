# RBAC Developer Guide

## Quick Start

### 1. Setup (First Time Only)
```bash
# Run migrations
php artisan migrate

# Seed permissions and roles
php artisan db:seed --class=PermissionSeeder

# Create your first super admin
php artisan make:super-admin
```

### 2. Login
Access the admin panel at `/admin` and login with your super admin credentials.

## Adding New Resources with Permissions

When you create a new Filament resource, follow these steps to add permission protection:

### Step 1: Add Permissions to Seeder

Edit `database/seeders/PermissionSeeder.php`:

```php
$resources = [
    'users' => ['view', 'create', 'update', 'delete'],
    'roles' => ['view', 'create', 'update', 'delete'],
    // ... existing resources ...
    'your_new_resource' => ['view', 'create', 'update', 'delete'], // Add this
];
```

### Step 2: Add Authorization Methods to Resource

In your resource file (e.g., `app/Filament/Resources/YourResource.php`):

```php
public static function canViewAny(): bool
{
    return auth()->user()->can('view_your_new_resource');
}

public static function canCreate(): bool
{
    return auth()->user()->can('create_your_new_resource');
}

public static function canEdit($record): bool
{
    return auth()->user()->can('update_your_new_resource');
}

public static function canDelete($record): bool
{
    return auth()->user()->can('delete_your_new_resource');
}
```

### Step 3: Re-seed Permissions

```bash
php artisan db:seed --class=PermissionSeeder
```

### Step 4: Update Existing Roles

Go to **User Management > Roles** and update roles to include the new permissions.

## Checking Permissions in Code

### In Controllers

```php
// Check single permission
if (auth()->user()->can('view_news')) {
    // User has permission
}

// Check multiple permissions (any)
if (auth()->user()->canAny(['view_news', 'create_news'])) {
    // User has at least one permission
}

// Throw exception if no permission
auth()->user()->authorize('update_news');
```

### In Blade Views

```blade
@can('view_news')
    <!-- Show content -->
@endcan

@canany(['create_news', 'update_news'])
    <!-- Show content -->
@endcanany
```

### In Routes

```php
Route::middleware(['auth', 'permission:view_news'])->group(function () {
    // Protected routes
});

Route::middleware(['auth', 'role:Admin'])->group(function () {
    // Admin only routes
});
```

## Working with Roles

### Check User Roles

```php
// Check if user has a role
if (auth()->user()->hasRole('Admin')) {
    // User is admin
}

// Check if user has any of the roles
if (auth()->user()->hasAnyRole(['Admin', 'Super Admin'])) {
    // User has at least one role
}

// Check if user has all roles
if (auth()->user()->hasAllRoles(['Admin', 'Editor'])) {
    // User has all specified roles
}
```

### Assign Roles Programmatically

```php
$user = User::find(1);

// Assign single role
$user->assignRole('Editor');

// Assign multiple roles
$user->assignRole(['Editor', 'Viewer']);

// Sync roles (removes all other roles)
$user->syncRoles(['Admin']);

// Remove role
$user->removeRole('Editor');
```

### Assign Permissions Directly

```php
$user = User::find(1);

// Give permission
$user->givePermissionTo('view_news');

// Give multiple permissions
$user->givePermissionTo(['view_news', 'create_news']);

// Revoke permission
$user->revokePermissionTo('create_news');

// Sync permissions
$user->syncPermissions(['view_news', 'update_news']);
```

## Custom Permission Logic

### Resource-Level Custom Authorization

For more complex authorization logic, you can override methods:

```php
public static function canEdit($record): bool
{
    $user = auth()->user();
    
    // Super admins can edit anything
    if ($user->hasRole('Super Admin')) {
        return true;
    }
    
    // Users can only edit their own records
    if ($user->can('update_news') && $record->created_by === $user->id) {
        return true;
    }
    
    return false;
}
```

### Using Policies

Create a policy for more complex authorization:

```bash
php artisan make:policy NewsPolicy --model=News
```

In the policy:

```php
public function update(User $user, News $news)
{
    // Super admins can update anything
    if ($user->hasRole('Super Admin')) {
        return true;
    }
    
    // Users can update their own news
    return $user->can('update_news') && $news->created_by === $user->id;
}
```

Register in `AuthServiceProvider`:

```php
protected $policies = [
    News::class => NewsPolicy::class,
];
```

## Permission Naming Convention

Follow this pattern for consistency:

- `view_{resource}` - List and view records
- `create_{resource}` - Create new records
- `update_{resource}` - Edit existing records
- `delete_{resource}` - Delete records
- `restore_{resource}` - Restore soft-deleted records
- `force_delete_{resource}` - Permanently delete records

Use snake_case for resource names (e.g., `contact_inquiries`, not `contactInquiries`).

## Testing Permissions

### Create Test Users with Roles

```php
// In your tests or seeders
$admin = User::factory()->create();
$admin->assignRole('Admin');

$editor = User::factory()->create();
$editor->assignRole('Editor');

$viewer = User::factory()->create();
$viewer->assignRole('Viewer');
```

### Test Authorization

```php
public function test_admin_can_create_news()
{
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    
    $this->actingAs($admin);
    
    $response = $this->post('/admin/news', [
        'title' => 'Test News',
        // ... other fields
    ]);
    
    $response->assertSuccessful();
}

public function test_viewer_cannot_create_news()
{
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');
    
    $this->actingAs($viewer);
    
    $response = $this->post('/admin/news', [
        'title' => 'Test News',
        // ... other fields
    ]);
    
    $response->assertForbidden();
}
```

## Troubleshooting

### Clear Permission Cache

If permissions aren't working after changes:

```bash
php artisan permission:cache-reset
```

### Reset All Permissions

To completely reset permissions and roles:

```bash
php artisan db:seed --class=PermissionSeeder
```

This will recreate all permissions and default roles.

### Check User Permissions

In Tinker:

```bash
php artisan tinker
```

```php
$user = User::find(1);
$user->getAllPermissions(); // Get all permissions
$user->getRoleNames(); // Get all roles
$user->can('view_news'); // Check specific permission
```

## Best Practices

1. **Always use permissions, not roles** in authorization checks
   - ✅ `auth()->user()->can('view_news')`
   - ❌ `auth()->user()->hasRole('Admin')`

2. **Use descriptive permission names**
   - ✅ `view_contact_inquiries`
   - ❌ `ci_view`

3. **Group related permissions**
   - Keep CRUD operations together for each resource

4. **Document custom permissions**
   - Add comments explaining non-standard permissions

5. **Test authorization thoroughly**
   - Test both positive and negative cases
   - Test edge cases and boundary conditions

## API Authentication with Permissions

If using Sanctum for API authentication:

```php
// In your API controller
public function index(Request $request)
{
    if (!$request->user()->can('view_news')) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    return News::all();
}
```

Or use middleware:

```php
Route::middleware(['auth:sanctum', 'permission:view_news'])->get('/news', [NewsController::class, 'index']);
```

## Additional Resources

- [Spatie Permission Package](https://spatie.be/docs/laravel-permission)
- [Filament Authorization](https://filamentphp.com/docs/3.x/panels/users#authorization)
- [Laravel Authorization](https://laravel.com/docs/authorization)
