# Queue Setup Guide

## Overview

Laravel queues allow you to defer time-consuming tasks (like sending emails) to be processed in the background, making your application more responsive.

## Development vs Production

### Development (Current Setup) ✅

Your `.env` is already configured for development:

```env
QUEUE_CONNECTION=sync
```

**What this means:**
- Emails are sent immediately (synchronously)
- No queue worker needed
- Simple and easy for local development
- Emails sent during the request

**Pros:**
- ✅ No additional processes to manage
- ✅ Easy to debug
- ✅ Works out of the box

**Cons:**
- ❌ Slower response times (user waits for email to send)
- ❌ Not suitable for production

### Production (Recommended)

For production, use the database queue driver with a queue worker:

```env
QUEUE_CONNECTION=database
```

**What this means:**
- Emails are queued in the database
- Queue worker processes them in the background
- Faster response times
- Better error handling and retries

## Setup for Production

### Option 1: Supervisor (Recommended)

Supervisor keeps your queue worker running continuously.

#### 1. Install Supervisor

**Ubuntu/Debian:**
```bash
sudo apt-get install supervisor
```

**CentOS/RHEL:**
```bash
sudo yum install supervisor
```

**macOS:**
```bash
brew install supervisor
```

#### 2. Create Configuration File

Copy the provided configuration:

```bash
sudo cp deployment/supervisor/laravel-worker.conf /etc/supervisor/conf.d/
```

#### 3. Update Configuration

Edit `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lgihe-backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/lgihe-backend/storage/logs/worker.log
stopwaitsecs=3600
```

**Important:** Update these paths:
- `/var/www/lgihe-backend` → Your actual application path
- `www-data` → Your web server user (might be `nginx`, `apache`, etc.)

#### 4. Start Supervisor

```bash
# Reload supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start the worker
sudo supervisorctl start laravel-worker:*

# Check status
sudo supervisorctl status
```

#### 5. Manage Workers

```bash
# Stop workers
sudo supervisorctl stop laravel-worker:*

# Restart workers (after code deployment)
sudo supervisorctl restart laravel-worker:*

# View logs
sudo supervisorctl tail -f laravel-worker:* stdout
```

### Option 2: Systemd Service

Alternative to Supervisor using systemd.

#### 1. Create Service File

Create `/etc/systemd/system/laravel-worker.service`:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/lgihe-backend
ExecStart=/usr/bin/php /var/www/lgihe-backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

#### 2. Enable and Start Service

```bash
# Reload systemd
sudo systemctl daemon-reload

# Enable service (start on boot)
sudo systemctl enable laravel-worker

# Start service
sudo systemctl start laravel-worker

# Check status
sudo systemctl status laravel-worker
```

#### 3. Manage Service

```bash
# Stop service
sudo systemctl stop laravel-worker

# Restart service
sudo systemctl restart laravel-worker

# View logs
sudo journalctl -u laravel-worker -f
```

### Option 3: Cron Job (Simple but not recommended)

For simple setups, you can use cron to process the queue periodically.

Add to crontab:

```bash
* * * * * cd /var/www/lgihe-backend && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

**Note:** This is less efficient than Supervisor/Systemd.

## Queue Configuration

### Update .env for Production

```env
QUEUE_CONNECTION=database
```

### Queue Worker Options

```bash
# Basic worker
php artisan queue:work

# With options
php artisan queue:work --sleep=3 --tries=3 --max-time=3600

# Process specific queue
php artisan queue:work --queue=emails,default

# Run once (for cron)
php artisan queue:work --stop-when-empty
```

**Options explained:**
- `--sleep=3` - Sleep 3 seconds when no jobs available
- `--tries=3` - Retry failed jobs 3 times
- `--max-time=3600` - Restart worker after 1 hour (prevents memory leaks)
- `--queue=emails,default` - Process specific queues in order

## Monitoring

### Check Queue Status

```bash
# View failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry {id}

# Retry all failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### Monitor Queue in Real-time

```bash
# Watch queue status
watch -n 1 'php artisan queue:monitor'
```

## Deployment Workflow

When deploying new code:

```bash
# 1. Pull new code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Run migrations
php artisan migrate --force

# 4. Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart queue workers
sudo supervisorctl restart laravel-worker:*
# OR
sudo systemctl restart laravel-worker
```

**Important:** Always restart queue workers after deployment to load new code!

## Troubleshooting

### Workers Not Processing Jobs

1. Check worker is running:
   ```bash
   sudo supervisorctl status
   # OR
   sudo systemctl status laravel-worker
   ```

2. Check logs:
   ```bash
   tail -f storage/logs/worker.log
   # OR
   sudo journalctl -u laravel-worker -f
   ```

3. Check failed jobs:
   ```bash
   php artisan queue:failed
   ```

### Emails Not Sending

1. Check queue connection:
   ```bash
   php artisan config:clear
   php artisan tinker --execute="echo config('queue.default');"
   ```

2. Check jobs table:
   ```bash
   php artisan tinker --execute="echo DB::table('jobs')->count();"
   ```

3. Manually process queue:
   ```bash
   php artisan queue:work --once
   ```

### Memory Issues

If workers consume too much memory:

1. Reduce `--max-time`:
   ```bash
   php artisan queue:work --max-time=1800
   ```

2. Add memory limit:
   ```bash
   php artisan queue:work --memory=128
   ```

3. Increase PHP memory limit in `php.ini`:
   ```ini
   memory_limit = 256M
   ```

## Best Practices

1. **Always restart workers after deployment**
2. **Monitor failed jobs regularly**
3. **Set up alerts for failed jobs**
4. **Use `--max-time` to prevent memory leaks**
5. **Run multiple workers for high traffic**
6. **Use separate queues for different priorities**
7. **Log worker output for debugging**

## For Your Current Setup

Since you're using `QUEUE_CONNECTION=sync` in development:

✅ **No action needed for local development**
- Emails send immediately
- No queue worker required
- Perfect for testing

⚠️ **For production deployment:**
1. Change to `QUEUE_CONNECTION=database`
2. Set up Supervisor (recommended)
3. Start queue workers
4. Monitor and maintain

## Quick Reference

```bash
# Development (current)
QUEUE_CONNECTION=sync  # No worker needed

# Production
QUEUE_CONNECTION=database  # Requires worker

# Start worker manually (testing)
php artisan queue:work

# Start worker with supervisor (production)
sudo supervisorctl start laravel-worker:*

# Restart after deployment
sudo supervisorctl restart laravel-worker:*

# Check status
sudo supervisorctl status

# View logs
tail -f storage/logs/worker.log
```

## Summary

- ✅ **Development**: Use `sync` driver (current setup) - no worker needed
- ✅ **Production**: Use `database` driver with Supervisor - requires worker
- ✅ **Deployment**: Always restart workers after code changes
- ✅ **Monitoring**: Check failed jobs and logs regularly
