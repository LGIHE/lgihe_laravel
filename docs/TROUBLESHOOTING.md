# 🔧 Troubleshooting Guide

Common issues and solutions for the LGIHE Backend system.

## 🔐 Authentication Issues

### Can't Login to Admin Panel

**Symptoms**: Login page shows but credentials don't work

**Solutions**:

1. **Reset Database**:
```bash
php artisan migrate:fresh --seed
php artisan make:super-admin
```

2. **Check User Exists**:
```bash
php artisan tinker
>>> User::where('email', 'admin@lgihe.ac.ug')->first()
```

3. **Reset Password**:
```bash
php artisan tinker
>>> $user = User::where('email', 'admin@lgihe.ac.ug')->first();
>>> $user->password = bcrypt('newpassword');
>>> $user->save();
```

### User Has No Access to Resources

**Symptoms**: User can login but sees empty admin panel

**Solutions**:

1. **Assign Role**:
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->assignRole('Admin');
```

2. **Check Permissions**:
```bash
>>> $user->roles;
>>> $user->permissions;
>>> $user->can('view_news');
```

3. **Reseed Permissions**:
```bash
php artisan db:seed --class=PermissionSeeder
php artisan permission:cache-reset
```

## 💾 Database Issues

### Database Connection Error

**Symptoms**: "SQLSTATE[HY000] [2002] Connection refused"

**Solutions**:

1. **Check .env Configuration**:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

2. **Test Connection**:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

3. **For SQLite**:
```bash
# Ensure file exists
touch database/database.sqlite

# Update .env
DB_CONNECTION=sqlite
```

### Migration Errors

**Symptoms**: "Table already exists" or "Column not found"

**Solutions**:

1. **Fresh Migration**:
```bash
php artisan migrate:fresh
```

2. **Rollback and Retry**:
```bash
php artisan migrate:rollback
php artisan migrate
```

3. **Check Migration Status**:
```bash
php artisan migrate:status
```

## 📁 File Upload Issues

### Images Not Uploading

**Symptoms**: Upload fails or images don't display

**Solutions**:

1. **Create Storage Link**:
```bash
php artisan storage:link
```

2. **Fix Permissions**:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

3. **Check Disk Configuration**:
```bash
php artisan tinker
>>> config('filesystems.default')
>>> Storage::disk('public')->exists('test.txt')
```

4. **Verify Upload Limits**:
```ini
# php.ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### Storage Link Broken

**Symptoms**: "The file does not exist" for uploaded images

**Solutions**:

1. **Remove and Recreate Link**:
```bash
rm public/storage
php artisan storage:link
```

2. **Manual Link (if command fails)**:
```bash
ln -s ../storage/app/public public/storage
```

## 🚀 Performance Issues

### Slow Admin Panel

**Symptoms**: Pages take long to load

**Solutions**:

1. **Clear All Caches**:
```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

2. **Optimize for Production**:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

3. **Check Database Indexes**:
```bash
php artisan tinker
>>> DB::select('SHOW INDEX FROM news');
```

### High Memory Usage

**Symptoms**: "Allowed memory size exhausted"

**Solutions**:

1. **Increase Memory Limit**:
```ini
# php.ini
memory_limit = 256M
```

2. **Optimize Queries**:
```php
// Use pagination
News::paginate(20);

// Eager load relationships
News::with('creator')->get();

// Select specific columns
News::select('id', 'title')->get();
```

## 📧 Email Issues

### Emails Not Sending

**Symptoms**: No emails received after actions

**Solutions**:

1. **Check Mail Configuration**:
```bash
php artisan tinker
>>> config('mail.mailer')
>>> config('mail.from.address')
```

2. **Test Email**:
```bash
php artisan tinker
>>> Mail::raw('Test email', function($msg) {
>>>     $msg->to('test@example.com')->subject('Test');
>>> });
```

3. **Check Resend API Key**:
```env
MAIL_MAILER=resend
RESEND_API_KEY=re_xxxxxxxxxxxxx
MAIL_FROM_ADDRESS=noreply@lgihe.ac.ug
```

4. **Check Logs**:
```bash
tail -f storage/logs/laravel.log
```

### Queue Jobs Not Processing

**Symptoms**: Emails queued but not sent

**Solutions**:

1. **Run Queue Worker**:
```bash
php artisan queue:work
```

2. **Check Failed Jobs**:
```bash
php artisan queue:failed
php artisan queue:retry all
```

3. **Use Sync Driver for Testing**:
```env
QUEUE_CONNECTION=sync
```

## 🔒 Permission Issues

### 403 Forbidden Errors

**Symptoms**: "You do not have permission to perform this action"

**Solutions**:

1. **Check User Permissions**:
```bash
php artisan tinker
>>> $user = auth()->user();
>>> $user->getAllPermissions();
>>> $user->can('view_news');
```

2. **Clear Permission Cache**:
```bash
php artisan permission:cache-reset
```

3. **Reseed Permissions**:
```bash
php artisan db:seed --class=PermissionSeeder
```

### Resources Not Showing in Menu

**Symptoms**: Menu items missing for user

**Solutions**:

1. **Check Resource Authorization**:
```php
// In Resource class
public static function canViewAny(): bool
{
    return auth()->user()->can('view_resource');
}
```

2. **Assign Permissions**:
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->givePermissionTo('view_news');
```

## 🌐 API Issues

### CORS Errors

**Symptoms**: "Access-Control-Allow-Origin" errors in browser

**Solutions**:

1. **Configure CORS**:
```php
// config/cors.php
'allowed_origins' => [
    'http://localhost:3000',
    'https://lgihe.org',
],
```

2. **Clear Config Cache**:
```bash
php artisan config:clear
```

### API Returns 401 Unauthorized

**Symptoms**: API requests fail with 401

**Solutions**:

1. **Check Token**:
```bash
# Ensure Bearer token is included
Authorization: Bearer {token}
```

2. **Generate New Token**:
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $token = $user->createToken('api-token')->plainTextToken;
>>> echo $token;
```

3. **Check Sanctum Configuration**:
```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost')),
```

## 🖥️ Server Issues

### Port Already in Use

**Symptoms**: "Address already in use"

**Solutions**:

1. **Use Different Port**:
```bash
php artisan serve --port=8002
```

2. **Kill Existing Process**:
```bash
# Find process
lsof -ti:8001

# Kill process
kill -9 $(lsof -ti:8001)
```

### 500 Internal Server Error

**Symptoms**: White page or generic error

**Solutions**:

1. **Enable Debug Mode**:
```env
APP_DEBUG=true
```

2. **Check Error Logs**:
```bash
tail -f storage/logs/laravel.log
```

3. **Check Web Server Logs**:
```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log
```

4. **Fix Permissions**:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 🧪 Testing Issues

### Tests Failing

**Symptoms**: PHPUnit tests fail

**Solutions**:

1. **Use Testing Database**:
```env
# .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

2. **Clear Test Cache**:
```bash
php artisan config:clear
php artisan cache:clear --env=testing
```

3. **Run Specific Test**:
```bash
php artisan test --filter=TestName
```

## 🔄 Cache Issues

### Changes Not Reflecting

**Symptoms**: Code changes don't appear

**Solutions**:

1. **Clear All Caches**:
```bash
php artisan optimize:clear
composer dump-autoload
```

2. **Disable Caching in Development**:
```env
APP_ENV=local
CACHE_DRIVER=array
```

3. **Clear Browser Cache**:
   - Hard refresh: Ctrl+Shift+R (Windows/Linux) or Cmd+Shift+R (Mac)

## 📦 Composer Issues

### Composer Install Fails

**Symptoms**: Dependency resolution errors

**Solutions**:

1. **Update Composer**:
```bash
composer self-update
```

2. **Clear Composer Cache**:
```bash
composer clear-cache
```

3. **Install with Specific PHP Version**:
```bash
/usr/bin/php8.3 /usr/bin/composer install
```

4. **Ignore Platform Requirements** (not recommended for production):
```bash
composer install --ignore-platform-reqs
```

## 🔍 Debugging Tips

### Enable Query Logging

```php
// In AppServiceProvider boot()
DB::listen(function($query) {
    Log::info($query->sql, $query->bindings);
});
```

### Check Environment

```bash
php artisan about
php artisan env
php -v
composer --version
```

### Inspect Configuration

```bash
php artisan tinker
>>> config('app.name')
>>> config('database.default')
>>> config('mail.mailer')
```

### Monitor Logs in Real-Time

```bash
tail -f storage/logs/laravel.log
```

## 📞 Still Need Help?

If you're still experiencing issues:

1. **Check Laravel Logs**: `storage/logs/laravel.log`
2. **Check Web Server Logs**: Apache/Nginx error logs
3. **Search Documentation**: Laravel and Filament docs
4. **Contact Support**: it@lgihe.ac.ug

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)
- [Laravel Debug Bar](https://github.com/barryvdh/laravel-debugbar)
- [Laravel Telescope](https://laravel.com/docs/telescope)

---

**Version**: 1.0.0  
**Last Updated**: May 5, 2026
