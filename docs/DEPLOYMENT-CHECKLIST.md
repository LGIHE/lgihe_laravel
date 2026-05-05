# Abuse Reporting System - Deployment Checklist

## Pre-Deployment Checklist

### ✅ Environment Configuration

- [ ] Set `RESEND_API_KEY` in `.env`
- [ ] Set `MAIL_FROM_ADDRESS="noreply@lgihe.ac.ug"`
- [ ] Set `MAIL_FROM_NAME="LGIHE Safeguarding"`
- [ ] Set `MAIL_MAILER=resend`
- [ ] Verify database connection settings
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_URL` to production URL

### ✅ Database Setup

- [ ] Run migrations: `php artisan migrate --force`
- [ ] Verify `abuse_reports` table exists
- [ ] Check indexes are created
- [ ] Verify foreign key constraints

### ✅ Email Service

- [ ] Verify sender domain in Resend dashboard
- [ ] Test email delivery to `safeguarding@lgihe.ac.ug`
- [ ] Confirm safeguarding email is monitored
- [ ] Set up email forwarding if needed
- [ ] Configure spam filters to allow emails from `noreply@lgihe.ac.ug`

### ✅ Queue Configuration

- [ ] Configure queue driver (database, redis, etc.)
- [ ] Set up queue worker process
- [ ] Configure Supervisor for queue worker (production)
- [ ] Test queue job processing
- [ ] Set up failed job monitoring

### ✅ Security

- [ ] Enable HTTPS only
- [ ] Add rate limiting to API endpoint
- [ ] Configure CORS if needed
- [ ] Review Filament access permissions
- [ ] Set up firewall rules
- [ ] Enable Laravel's security features

### ✅ Testing

- [ ] Test anonymous report submission
- [ ] Test identified report submission
- [ ] Test validation errors
- [ ] Test email delivery
- [ ] Test admin panel access
- [ ] Test report viewing and editing
- [ ] Test filtering and searching
- [ ] Run automated tests: `php artisan test --filter=AbuseReportTest`

### ✅ Monitoring

- [ ] Set up application monitoring
- [ ] Configure error tracking (Sentry, Bugsnag, etc.)
- [ ] Set up email delivery monitoring
- [ ] Configure queue monitoring
- [ ] Set up uptime monitoring
- [ ] Create alerts for failed jobs

### ✅ Documentation

- [ ] Share API documentation with frontend team
- [ ] Train safeguarding team on admin panel
- [ ] Document incident response procedures
- [ ] Create user guide for report submission
- [ ] Document escalation procedures

---

## Deployment Steps

### 1. Code Deployment

```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Database Migration

```bash
# Run migrations
php artisan migrate --force

# Verify migration
php artisan migrate:status
```

### 3. Queue Worker Setup

#### Using Supervisor (Recommended for Production)

Create `/etc/supervisor/conf.d/lgihe-queue-worker.conf`:

```ini
[program:lgihe-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/lgihe_backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/lgihe_backend/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start lgihe-queue-worker:*
```

### 4. Permissions

```bash
# Set correct permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Test Deployment

```bash
# Test API endpoint
curl -X POST https://your-domain.com/api/v1/report-abuse \
  -H "Content-Type: application/json" \
  -d '{
    "anonymousReport": true,
    "incidentType": "bullying",
    "incidentDate": "2026-05-01",
    "incidentLocation": "Test Location",
    "personsInvolved": "Test Person",
    "detailedDescription": "Test deployment"
  }'

# Check queue worker
php artisan queue:work --once

# Check logs
tail -f storage/logs/laravel.log
```

---

## Post-Deployment Checklist

### ✅ Immediate (Within 1 Hour)

- [ ] Verify API endpoint is accessible
- [ ] Test report submission end-to-end
- [ ] Confirm email delivery
- [ ] Check admin panel access
- [ ] Monitor error logs
- [ ] Verify queue worker is running

### ✅ First Day

- [ ] Monitor email delivery rate
- [ ] Check for any errors in logs
- [ ] Verify database writes
- [ ] Test all incident types
- [ ] Check admin panel functionality
- [ ] Monitor API response times

### ✅ First Week

- [ ] Review all submitted reports
- [ ] Check email delivery success rate
- [ ] Monitor queue job failures
- [ ] Review error logs
- [ ] Gather feedback from safeguarding team
- [ ] Check database performance

### ✅ First Month

- [ ] Analyze report submission patterns
- [ ] Review incident type distribution
- [ ] Check system performance
- [ ] Update documentation if needed
- [ ] Train additional staff if needed
- [ ] Review security measures

---

## Configuration Files

### .env (Production)

```env
APP_NAME="LGIHE"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lgihe_production
DB_USERNAME=lgihe_user
DB_PASSWORD=secure_password

# Email
MAIL_MAILER=resend
RESEND_API_KEY=your_production_resend_key
MAIL_FROM_ADDRESS="noreply@lgihe.ac.ug"
MAIL_FROM_NAME="LGIHE Safeguarding"

# Queue
QUEUE_CONNECTION=database

# Cache
CACHE_STORE=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Rate Limiting (routes/api.php)

```php
// Add rate limiting to abuse report endpoint
Route::middleware('throttle:10,1')->group(function () {
    Route::post('v1/report-abuse', [AbuseReportController::class, 'store']);
});
```

---

## Monitoring Setup

### 1. Application Monitoring

```bash
# Install Laravel Telescope (optional, for development/staging)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 2. Error Tracking

```bash
# Install Sentry (recommended)
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=your_sentry_dsn
```

### 3. Queue Monitoring

```bash
# Monitor failed jobs
php artisan queue:failed

# Set up cron job to alert on failed jobs
# Add to crontab:
# */5 * * * * cd /path/to/lgihe_backend && php artisan queue:failed | mail -s "Failed Queue Jobs" admin@lgihe.ac.ug
```

### 4. Uptime Monitoring

Use services like:
- UptimeRobot
- Pingdom
- StatusCake

Monitor:
- `https://your-domain.com/api/v1/report-abuse` (POST endpoint)
- `https://your-domain.com/admin` (Admin panel)

---

## Backup Strategy

### Database Backups

```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u lgihe_user -p lgihe_production abuse_reports > /backups/abuse_reports_$DATE.sql
gzip /backups/abuse_reports_$DATE.sql

# Keep only last 30 days
find /backups -name "abuse_reports_*.sql.gz" -mtime +30 -delete
```

Add to crontab:
```
0 2 * * * /path/to/backup-script.sh
```

### Email Logs

- Configure Resend to retain email logs
- Set up log forwarding to external service
- Archive email logs monthly

---

## Security Hardening

### 1. HTTPS Configuration

```nginx
# Nginx configuration
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;

    # Force HTTPS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location /api/v1/report-abuse {
        # Rate limiting
        limit_req zone=api burst=5 nodelay;
        
        # CORS headers (if needed)
        add_header Access-Control-Allow-Origin "https://your-frontend-domain.com" always;
        add_header Access-Control-Allow-Methods "POST, OPTIONS" always;
        add_header Access-Control-Allow-Headers "Content-Type" always;
        
        # Proxy to Laravel
        proxy_pass http://127.0.0.1:8000;
    }
}
```

### 2. Rate Limiting

```php
// config/app.php - Add custom rate limiter
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('abuse-reports', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});
```

### 3. Firewall Rules

```bash
# Allow only necessary ports
sudo ufw allow 22/tcp  # SSH
sudo ufw allow 80/tcp  # HTTP
sudo ufw allow 443/tcp # HTTPS
sudo ufw enable
```

---

## Rollback Plan

### If Issues Occur

1. **Stop Queue Worker**
   ```bash
   sudo supervisorctl stop lgihe-queue-worker:*
   ```

2. **Rollback Code**
   ```bash
   git checkout previous-stable-tag
   composer install --no-dev --optimize-autoloader
   ```

3. **Rollback Database** (if needed)
   ```bash
   php artisan migrate:rollback --step=1
   ```

4. **Clear Caches**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

5. **Restart Services**
   ```bash
   sudo supervisorctl start lgihe-queue-worker:*
   sudo systemctl restart php8.2-fpm
   sudo systemctl restart nginx
   ```

---

## Support Contacts

### Technical Team
- **Lead Developer**: [Name] - [Email]
- **System Administrator**: [Name] - [Email]
- **Database Administrator**: [Name] - [Email]

### Safeguarding Team
- **Primary Contact**: safeguarding@lgihe.ac.ug
- **Emergency**: (+256) 414 222 517

### External Services
- **Resend Support**: support@resend.com
- **Hosting Provider**: [Provider Contact]

---

## Emergency Procedures

### If Email Service Fails

1. Check Resend dashboard for status
2. Verify API key is valid
3. Check queue for failed jobs
4. Manually notify safeguarding team
5. Switch to backup email service if needed

### If Database Fails

1. Check database connection
2. Verify credentials
3. Check disk space
4. Restore from backup if needed
5. Contact database administrator

### If Queue Worker Stops

1. Check supervisor status
2. Review queue worker logs
3. Restart queue worker
4. Process failed jobs manually
5. Investigate root cause

---

## Success Criteria

### Deployment is Successful When:

- ✅ API endpoint responds correctly
- ✅ Reports are saved to database
- ✅ Emails are delivered to safeguarding team
- ✅ Admin panel is accessible
- ✅ Queue worker is processing jobs
- ✅ No errors in logs
- ✅ All tests pass
- ✅ Monitoring is active

---

## Final Sign-Off

### Deployment Approval

- [ ] **Technical Lead**: _________________ Date: _______
- [ ] **System Administrator**: _________________ Date: _______
- [ ] **Safeguarding Team Lead**: _________________ Date: _______
- [ ] **IT Director**: _________________ Date: _______

### Post-Deployment Review

- [ ] **24 Hours**: System stable, no critical issues
- [ ] **1 Week**: All features working as expected
- [ ] **1 Month**: Performance meets requirements

---

**Document Version**: 1.0.0  
**Last Updated**: May 5, 2026  
**Next Review**: June 5, 2026
