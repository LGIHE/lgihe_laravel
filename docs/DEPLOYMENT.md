# LGIHE Admin Panel - cPanel Deployment Guide

## Prerequisites

- cPanel access with SSH/Terminal
- Git repository (GitHub, GitLab, or Bitbucket)
- MySQL database created in cPanel
- PHP 8.2+ available on server
- Composer installed on server

## Initial Deployment

### 1. Create Subdomain in cPanel

1. Log into cPanel
2. Navigate to **Domains** → **Subdomains**
3. Create subdomain: `admin`
4. Domain: `lgihe.org`
5. Document Root: `/home/kqvxxfkj/admin.lgihe.org/public` ⚠️ **Important: Must point to `/public`**

### 2. Create Database

1. Go to **MySQL Databases** in cPanel
2. Create database: `kqvxxfkj_lgihe_web` (or similar)
3. Create database user with strong password
4. Add user to database with ALL PRIVILEGES
5. Note down: database name, username, password

### 3. Clone Repository

Open cPanel Terminal and run:

```bash
cd /home/kqvxxfkj
git clone <your-repository-url> admin.lgihe.org
cd admin.lgihe.org
```

### 4. Install Dependencies

```bash
# Find your PHP 8.4 path (or use ea-php84)
which php8.4

# Install Composer dependencies
/usr/bin/php8.4 /usr/bin/composer install --no-dev --optimize-autoloader
```

### 5. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit with nano or vim
nano .env
```

**Critical `.env` Settings:**

```env
APP_NAME="LGIHE Admin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.lgihe.org

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=kqvxxfkj_lgihe_web
DB_USERNAME=kqvxxfkj_dbuser
DB_PASSWORD=your_secure_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mail.lgihe.org
MAIL_PORT=465
MAIL_USERNAME=noreply@lgihe.org
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@lgihe.org
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Generate Application Key

```bash
php artisan key:generate
```

### 7. Run Database Migrations

```bash
php artisan migrate --force
```

### 8. Create Storage Link

```bash
php artisan storage:link
```

### 9. Set Permissions

```bash
chmod -R 755 /home/kqvxxfkj/admin.lgihe.org
chmod -R 775 /home/kqvxxfkj/admin.lgihe.org/storage
chmod -R 775 /home/kqvxxfkj/admin.lgihe.org/bootstrap/cache
```

### 10. Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 11. Create Admin User

```bash
php artisan tinker
```

Then in tinker:

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@lgihe.org';
$user->password = bcrypt('your-secure-password');
$user->save();
exit
```

### 12. Set Up SSL Certificate

1. In cPanel, go to **SSL/TLS Status**
2. Find `admin.lgihe.org`
3. Click **Run AutoSSL** (if using Let's Encrypt)
4. Or install custom SSL certificate

## Subsequent Deployments

For future updates, simply run:

```bash
cd /home/kqvxxfkj/admin.lgihe.org
bash deploy.sh
```

Or manually:

```bash
cd /home/kqvxxfkj/admin.lgihe.org
git pull origin main
/usr/bin/php8.4 /usr/bin/composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### 500 Internal Server Error

1. Check error logs: `tail -f /home/kqvxxfkj/admin.lgihe.org/storage/logs/laravel.log`
2. Verify `.env` file exists and has correct settings
3. Check permissions on `storage` and `bootstrap/cache`
4. Clear all caches: `php artisan optimize:clear`

### Database Connection Error

1. Verify database credentials in `.env`
2. Test connection: `php artisan tinker` then `DB::connection()->getPdo();`
3. Check if database user has correct privileges

### Composer Install Fails

1. Check PHP version: `php -v`
2. Use correct PHP binary: `/usr/bin/php8.4 /usr/bin/composer install`
3. Increase memory limit if needed

### Permission Denied Errors

```bash
chmod -R 775 storage bootstrap/cache
chown -R kqvxxfkj:kqvxxfkj /home/kqvxxfkj/admin.lgihe.org
```

### Git Pull Fails

```bash
# Reset local changes
git reset --hard origin/main
git pull origin main
```

## Maintenance Mode

Enable maintenance mode during updates:

```bash
php artisan down --secret="your-secret-token"
# Perform updates
php artisan up
```

Access site during maintenance: `https://admin.lgihe.org/your-secret-token`

## Monitoring

- **Error Logs**: `/home/kqvxxfkj/admin.lgihe.org/storage/logs/laravel.log`
- **cPanel Error Logs**: cPanel → **Metrics** → **Errors**
- **PHP Error Logs**: Check cPanel error_log files

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secured
- [ ] `.env` file not in Git repository
- [ ] SSL certificate installed
- [ ] File permissions set correctly (755/775)
- [ ] Regular backups configured
- [ ] Firewall rules configured (if applicable)

## Backup Strategy

### Manual Backup

```bash
# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf backup_$(date +%Y%m%d).tar.gz /home/kqvxxfkj/admin.lgihe.org
```

### Automated Backups

Use cPanel's **Backup Wizard** to schedule automatic backups.

## Support

For issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. cPanel error logs
3. PHP error logs
4. Server error logs

## Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Filament Documentation](https://filamentphp.com/docs)
- cPanel Documentation
