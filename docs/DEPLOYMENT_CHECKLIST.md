# Tender Functionality Deployment Checklist

## Issue: Tenders menu not appearing and 403 error

This happens when permissions are not properly seeded on the server.

## Deployment Steps (Run on Server)

### 1. Pull Latest Changes
```bash
git pull origin main
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. **IMPORTANT: Seed Permissions**
```bash
php artisan db:seed --class=PermissionSeeder
```

This will create the tender permissions and assign them to roles.

### 4. Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### 5. Clear Permission Cache (Very Important!)
```bash
php artisan permission:cache-reset
```

This is crucial because Spatie Permission package caches permissions.

### 6. Verify Permissions Exist
```bash
php artisan tinker
```

Then run:
```php
\Spatie\Permission\Models\Permission::where('name', 'like', '%tender%')->get();
```

You should see:
- view_tenders
- create_tenders
- update_tenders
- delete_tenders

### 7. Verify Super Admin Has Permissions
```bash
php artisan tinker
```

Then run:
```php
$superAdmin = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
$superAdmin->permissions->pluck('name')->toArray();
```

This should include all tender permissions.

### 8. Verify Your User Has Super Admin Role
```bash
php artisan tinker
```

Then run:
```php
$user = \App\Models\User::where('email', 'your-email@example.com')->first();
$user->roles->pluck('name')->toArray();
```

Should return: `["Super Admin"]`

## Quick Fix Command (Run All at Once)

```bash
php artisan migrate && \
php artisan db:seed --class=PermissionSeeder && \
php artisan permission:cache-reset && \
php artisan cache:clear && \
php artisan config:clear && \
php artisan route:clear && \
php artisan view:clear && \
php artisan optimize:clear
```

## If Still Not Working

### Option 1: Manually Assign Permissions to Super Admin
```bash
php artisan tinker
```

```php
$superAdmin = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
$tenderPermissions = \Spatie\Permission\Models\Permission::where('name', 'like', '%tender%')->get();
$superAdmin->givePermissionTo($tenderPermissions);
echo "Permissions assigned!";
```

### Option 2: Re-sync All Permissions for Super Admin
```bash
php artisan tinker
```

```php
$superAdmin = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
$allPermissions = \Spatie\Permission\Models\Permission::all();
$superAdmin->syncPermissions($allPermissions);
echo "All permissions synced to Super Admin!";
```

### Option 3: Clear Browser Cache
Sometimes the issue is browser cache:
- Hard refresh: `Ctrl + Shift + R` (Windows/Linux) or `Cmd + Shift + R` (Mac)
- Or clear browser cache completely

## Verification

After running the commands, verify:

1. **Check if permissions exist:**
```bash
php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::where('name', 'like', '%tender%')->count();"
```
Should return: `4`

2. **Check if Super Admin has tender permissions:**
```bash
php artisan tinker --execute="echo \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first()->hasPermissionTo('view_tenders') ? 'YES' : 'NO';"
```
Should return: `YES`

3. **Check if your user has Super Admin role:**
```bash
php artisan tinker --execute="echo \App\Models\User::find(1)->hasRole('Super Admin') ? 'YES' : 'NO';"
```
Should return: `YES`

## Common Issues

### Issue 1: Permission Cache Not Cleared
**Solution:** Run `php artisan permission:cache-reset`

### Issue 2: Config Cache Preventing New Routes
**Solution:** Run `php artisan config:clear` and `php artisan route:clear`

### Issue 3: Permissions Not Seeded
**Solution:** Run `php artisan db:seed --class=PermissionSeeder`

### Issue 4: Super Admin Role Doesn't Have New Permissions
**Solution:** Re-run the PermissionSeeder or manually assign permissions

### Issue 5: User Not Logged Out/In After Permission Changes
**Solution:** Log out and log back in to refresh session

## Production Environment Considerations

If you're using a queue worker or supervisor:
```bash
php artisan queue:restart
sudo supervisorctl restart all
```

If you're using OPcache:
```bash
php artisan optimize:clear
# Or restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

## Final Check

After all steps, try:
1. Log out of Filament admin panel
2. Clear browser cache
3. Log back in
4. Navigate to `/admin/tenders`

The Tenders menu should now appear in the sidebar and you should have full access.
