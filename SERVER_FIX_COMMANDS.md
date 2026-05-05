# Quick Fix for Tender Menu Not Appearing (Server)

## Problem
- Tenders menu not showing in Filament dashboard
- Getting 403 error when accessing `/admin/tenders`
- You are logged in as Super Admin

## Root Cause
Permissions not seeded or cached on the server after deployment.

## Quick Fix (Run on Server)

### Option 1: Automatic Fix (Recommended)
```bash
# Pull latest changes (includes fix commands)
git pull origin main

# Run the automatic fix command
php artisan tender:fix-permissions
```

This command will:
- Create tender permissions if missing
- Assign permissions to all roles
- Clear permission cache
- Clear application caches

### Option 2: Manual Fix
```bash
# Pull latest changes
git pull origin main

# Run migration
php artisan migrate

# Seed permissions
php artisan db:seed --class=PermissionSeeder

# Clear permission cache (VERY IMPORTANT!)
php artisan permission:cache-reset

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Option 3: One-Line Fix
```bash
git pull origin main && php artisan migrate && php artisan db:seed --class=PermissionSeeder && php artisan permission:cache-reset && php artisan optimize:clear
```

## Verify the Fix

### Check if permissions exist:
```bash
php artisan tender:diagnose
```

This will show you:
- If tender permissions exist
- If Super Admin has tender permissions
- If your user has the right roles
- Recommendations for fixing issues

### Check specific user:
```bash
php artisan tender:diagnose 1
```
(Replace `1` with your user ID)

## After Running Commands

1. **Log out** of Filament admin panel
2. **Clear browser cache** (Ctrl+Shift+R or Cmd+Shift+R)
3. **Log back in**
4. Navigate to `/admin/tenders`

The Tenders menu should now appear in the sidebar!

## If Still Not Working

### Check if you're really Super Admin:
```bash
php artisan tinker
```

Then run:
```php
$user = \App\Models\User::where('email', 'your-email@example.com')->first();
echo $user->hasRole('Super Admin') ? 'YES - You are Super Admin' : 'NO - You are NOT Super Admin';
exit;
```

### Manually assign Super Admin role:
```bash
php artisan tinker
```

Then run:
```php
$user = \App\Models\User::where('email', 'your-email@example.com')->first();
$superAdmin = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
$user->assignRole($superAdmin);
echo "Super Admin role assigned!";
exit;
```

## Production Server Considerations

If using queue workers:
```bash
php artisan queue:restart
```

If using supervisor:
```bash
sudo supervisorctl restart all
```

If using PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```
(Adjust PHP version as needed)

## Common Mistakes

❌ **Forgetting to clear permission cache**
```bash
php artisan permission:cache-reset
```

❌ **Not logging out and back in**
- Session needs to be refreshed

❌ **Browser cache not cleared**
- Hard refresh: Ctrl+Shift+R

❌ **Running commands in wrong directory**
- Make sure you're in the Laravel project root

## Success Indicators

✅ `php artisan tender:diagnose` shows all green checkmarks  
✅ Tenders menu appears in Filament sidebar  
✅ Can access `/admin/tenders` without 403 error  
✅ Can create, edit, and delete tenders  

## Need More Help?

Run the diagnostic command for detailed information:
```bash
php artisan tender:diagnose
```

This will tell you exactly what's wrong and how to fix it.
