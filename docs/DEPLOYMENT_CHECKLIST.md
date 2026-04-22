# Deployment Checklist

## Pre-Deployment Verification

### ✅ Database Migrations
- [x] Analytics tables updated with new schema
- [x] All migrations run successfully
- [x] Data preserved from old schema

### ✅ Code Changes
- [x] Events Resource updated with RichEditor and status dropdown
- [x] News Resource updated with RichEditor and status dropdown
- [x] Job Listings Resource updated with RichEditor and status dropdown
- [x] Create pages updated with publish confirmation modal
- [x] Analytics Controller updated to match API documentation
- [x] Analytics Models updated with new fields
- [x] Dashboard widgets created and registered

### ✅ API Endpoints
- [x] POST /api/v1/analytics/event
- [x] POST /api/v1/analytics/pageload
- [x] POST /api/v1/analytics/error

---

## Deployment Steps

### 1. Backup Database
```bash
# For SQLite
cp database/database.sqlite database/database.sqlite.backup

# For MySQL/PostgreSQL
# Use your database backup tool
```

### 2. Pull Latest Code
```bash
git pull origin main
```

### 3. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 4. Run Migrations
```bash
php artisan migrate --force
```

### 5. Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

### 6. Set Permissions (if needed)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Post-Deployment Verification

### Test Content Management

#### Events
- [ ] Create a new event
- [ ] Use rich text editor formatting
- [ ] Save as draft
- [ ] Verify draft is not visible on frontend
- [ ] Publish the event
- [ ] Verify event appears on frontend
- [ ] Check status badge colors

#### News
- [ ] Create a new article
- [ ] Use rich text editor formatting
- [ ] Save as draft
- [ ] Publish the article
- [ ] Verify article appears on frontend
- [ ] Check status badge colors

#### Job Listings
- [ ] Create a new job listing
- [ ] Use rich text editor formatting
- [ ] Save as draft
- [ ] Publish the job
- [ ] Verify job appears on frontend
- [ ] Check status badge colors

### Test Analytics Integration

#### Test Event Tracking
```bash
curl -X POST https://your-domain.com/api/v1/analytics/event \
  -H "Content-Type: application/json" \
  -d '{
    "name": "page_view",
    "properties": {"page": "/test"},
    "timestamp": "2026-04-22T10:30:00.000Z",
    "sessionId": "test_123"
  }'
```
Expected: `{"success":true}`

#### Test Page Load Tracking
```bash
curl -X POST https://your-domain.com/api/v1/analytics/pageload \
  -H "Content-Type: application/json" \
  -d '{
    "url": "/test",
    "loadTime": 1500,
    "timestamp": "2026-04-22T10:30:00.000Z",
    "sessionId": "test_123"
  }'
```
Expected: `{"success":true}`

#### Test Error Logging
```bash
curl -X POST https://your-domain.com/api/v1/analytics/error \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Test error",
    "severity": "low",
    "timestamp": "2026-04-22T10:30:00.000Z"
  }'
```
Expected: `{"success":true}`

### Verify Dashboard Widgets

- [ ] Login to admin panel
- [ ] Navigate to Dashboard
- [ ] Verify "Analytics Overview" widget displays
- [ ] Verify "Top Pages" widget displays
- [ ] Verify "Traffic by Country" widget displays
- [ ] Verify "Recent Errors" widget displays
- [ ] Check that widgets show data (or "No data" if empty)

---

## Frontend Integration

### Next.js Configuration

1. **Set Environment Variable**
   ```bash
   # .env.local or .env.production
   NEXT_PUBLIC_API_URL=https://your-domain.com/api/v1
   ```

2. **Verify Analytics Tracking**
   - [ ] Page views are being tracked
   - [ ] Page load times are being recorded
   - [ ] Errors are being logged
   - [ ] Data appears in admin dashboard

3. **Test Privacy Compliance**
   - [ ] Analytics consent is respected
   - [ ] Performance consent is respected
   - [ ] Geolocation consent is respected
   - [ ] Dashboard routes are not tracked

---

## CORS Configuration

If frontend and backend are on different domains:

### Laravel CORS Setup

1. **Update `config/cors.php`**:
   ```php
   'paths' => ['api/*', 'sanctum/csrf-cookie'],
   'allowed_origins' => [
       'https://your-frontend-domain.com',
       'http://localhost:3000', // For development
   ],
   'allowed_methods' => ['*'],
   'allowed_headers' => ['*'],
   'exposed_headers' => [],
   'max_age' => 0,
   'supports_credentials' => false,
   ```

2. **Test CORS**:
   ```bash
   curl -H "Origin: https://your-frontend-domain.com" \
        -H "Access-Control-Request-Method: POST" \
        -H "Access-Control-Request-Headers: Content-Type" \
        -X OPTIONS \
        https://your-backend-domain.com/api/v1/analytics/event
   ```

---

## Security Checklist

### Rate Limiting

Add to `app/Http/Kernel.php`:
```php
'api' => [
    'throttle:60,1', // 60 requests per minute
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

Or create custom rate limit for analytics:
```php
// In RouteServiceProvider
RateLimiter::for('analytics', function (Request $request) {
    return Limit::perMinute(100)->by($request->ip());
});
```

### Environment Variables

Ensure these are set in production:
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

### SSL/HTTPS

- [ ] SSL certificate installed
- [ ] HTTPS enforced
- [ ] Mixed content warnings resolved

---

## Monitoring

### What to Monitor

1. **Analytics Endpoint Health**
   - Response times
   - Error rates
   - Request volume

2. **Database Performance**
   - Query execution times
   - Table sizes
   - Index usage

3. **Storage Growth**
   - Analytics tables size
   - Growth rate
   - Cleanup effectiveness

### Set Up Alerts

- [ ] Alert when error rate > 5%
- [ ] Alert when response time > 2 seconds
- [ ] Alert when disk usage > 80%
- [ ] Alert when critical errors occur

---

## Data Retention

### Recommended Retention Policies

```php
// app/Console/Commands/CleanupAnalytics.php
// Run daily via cron

// Keep events for 90 days
AnalyticsEvent::where('created_at', '<', now()->subDays(90))->delete();

// Keep page loads for 90 days
PageLoad::where('created_at', '<', now()->subDays(90))->delete();

// Keep errors for 30 days (archive critical ones separately)
AnalyticsError::where('created_at', '<', now()->subDays(30))
    ->where('severity', '!=', 'critical')
    ->delete();
```

### Schedule in `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('analytics:cleanup')->daily();
}
```

---

## Rollback Plan

If issues occur after deployment:

### 1. Restore Database
```bash
# For SQLite
cp database/database.sqlite.backup database/database.sqlite

# For MySQL/PostgreSQL
# Use your database restore tool
```

### 2. Revert Code
```bash
git revert HEAD
# or
git reset --hard <previous-commit-hash>
```

### 3. Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Support Contacts

- **Technical Issues**: [IT Support Email]
- **Content Questions**: [Content Team Email]
- **Emergency**: [Emergency Contact]

---

## Documentation References

- [Implementation Summary](./IMPLEMENTATION_SUMMARY.md)
- [Content Editor Guide](./CONTENT_EDITOR_GUIDE.md)
- [Analytics Integration](./ANALYTICS_INTEGRATION.md)
- [Analytics Quick Reference](./ANALYTICS_QUICK_REFERENCE.md)

---

## Sign-Off

- [ ] Development Team Lead
- [ ] QA Team Lead
- [ ] System Administrator
- [ ] Project Manager

**Deployment Date**: _______________
**Deployed By**: _______________
**Verified By**: _______________

---

**Last Updated**: April 22, 2026
