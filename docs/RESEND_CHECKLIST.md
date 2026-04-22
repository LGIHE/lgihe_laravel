# Resend Email Integration - Setup Checklist

Use this checklist to ensure the Resend email integration is properly configured.

## ✅ Installation (Completed)

- [x] Resend PHP package installed (`resend/resend-php`)
- [x] Notification class created
- [x] Web controller created for password setup UI
- [x] API controller created (optional, for future use)
- [x] Web routes registered
- [x] Blade views created (auth layout, setup form, success, error)
- [x] User resource updated
- [x] Configuration files updated

## ⏳ Configuration (To Do)

### 1. Resend Account Setup
- [ ] Create account at [resend.com](https://resend.com)
- [ ] Generate API key in Resend dashboard
- [ ] Add domain to Resend (e.g., `lgihe.org`)
- [ ] Configure DNS records for domain verification
- [ ] Wait for domain verification (usually 5-10 minutes)

### 2. Environment Configuration
- [ ] Update `.env` file with:
  ```env
  MAIL_MAILER=resend
  RESEND_API_KEY=re_your_actual_api_key_here
  MAIL_FROM_ADDRESS="noreply@lgihe.org"
  MAIL_FROM_NAME="LGIHE Backend"
  FRONTEND_URL=https://www.lgihe.ac.ug
  ```

### 3. Queue Configuration
Choose one option:

**Option A: Use Queue Worker (Recommended for Production)**
- [ ] Ensure `QUEUE_CONNECTION=database` in `.env`
- [ ] Run queue worker: `php artisan queue:work --daemon`
- [ ] Set up supervisor or systemd to keep queue worker running

**Option B: Use Sync Driver (Simple for Development)**
- [ ] Set `QUEUE_CONNECTION=sync` in `.env`
- [ ] Emails will be sent immediately (slower but simpler)

### 4. Testing
- [ ] Test with log mailer first:
  - Set `MAIL_MAILER=log` temporarily
  - Create a test user
  - Check `storage/logs/laravel.log` for email content
  
- [ ] Test with Resend:
  - Set `MAIL_MAILER=resend`
  - Create a test user with your email
  - Check your inbox for the email
  - Click the link and verify it works
  - Test password setup flow

### 5. Frontend Implementation
- [ ] **NOT REQUIRED** - Password setup is handled by backend Blade views
- [ ] Users receive email with link to backend `/set-password` route
- [ ] No frontend integration needed

### 6. Production Deployment
- [ ] Verify all environment variables are set
- [ ] Ensure domain is verified in Resend
- [ ] Queue worker is running
- [ ] Test email delivery in production
- [ ] Monitor Resend dashboard for delivery status
- [ ] Set up error monitoring/alerts

## 📋 Quick Commands

### Check Configuration
```bash
# Check if Resend package is installed
composer show resend/resend-php

# Check routes are registered
php artisan route:list --path=api/v1/password

# Check notification class exists
php artisan tinker --execute="echo class_exists('App\Notifications\UserCreatedNotification') ? 'OK' : 'Missing';"

# Test queue connection
php artisan queue:work --once
```

### Testing Commands
```bash
# Clear config cache
php artisan config:clear

# View logs
tail -f storage/logs/laravel.log

# Test email (create test user via Filament)
# Then check logs or inbox
```

### Production Commands
```bash
# Cache configuration
php artisan config:cache

# Start queue worker
php artisan queue:work --daemon --tries=3 --timeout=90

# Monitor queue
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed
```

## 🔍 Verification Steps

### 1. Verify Package Installation
```bash
composer show resend/resend-php
```
Should show version 1.3.0 or higher.

### 2. Verify Routes
```bash
php artisan route:list --path=api/v1/password
```
Should show:
- `POST api/v1/password/verify-token`
- `POST api/v1/password/setup`

### 3. Verify Configuration
```bash
php artisan tinker
```
Then run:
```php
config('mail.mailers.resend');
config('services.resend.key');
config('app.frontend_url');
```

### 4. Create Test User
1. Go to admin panel: `http://localhost:8001/admin`
2. Navigate to Users
3. Click "New User"
4. Fill in details (use your email)
5. Leave password empty
6. Click "Create"
7. Check for success notification
8. Check your email inbox

## 🚨 Troubleshooting

### Issue: Emails not sending
**Check:**
- [ ] `RESEND_API_KEY` is set correctly
- [ ] Domain is verified in Resend dashboard
- [ ] `MAIL_FROM_ADDRESS` uses verified domain
- [ ] Queue worker is running (if using queue)
- [ ] Check `storage/logs/laravel.log` for errors

### Issue: Token expired
**Solution:**
- Tokens expire after 60 minutes
- Admin can edit user and save to trigger new email
- Or implement "Resend Email" button in frontend

### Issue: Frontend not working
**Not Applicable:**
- Password setup is handled entirely on the backend
- No frontend integration required
- Users access `/set-password` route on backend application

### Issue: Queue not processing
**Check:**
- [ ] Queue worker is running: `ps aux | grep queue:work`
- [ ] Database has `jobs` table
- [ ] Check failed jobs: `php artisan queue:failed`
- [ ] Restart queue worker: `php artisan queue:restart`

## 📚 Documentation

- **Complete Guide**: `docs/RESEND_INTEGRATION.md`
- **Quick Summary**: `docs/RESEND_SETUP_SUMMARY.md`
- **Setup Checklist**: `docs/RESEND_CHECKLIST.md`
- **Resend Docs**: https://resend.com/docs
- **Laravel Mail**: https://laravel.com/docs/mail

## 🎯 Success Criteria

You'll know the integration is working when:
- ✅ Creating a user without password shows success message
- ✅ Email arrives in user's inbox within seconds
- ✅ Email link opens backend password setup page
- ✅ Password setup form displays correctly
- ✅ Password setup completes successfully
- ✅ User is automatically logged in
- ✅ User is redirected to success page
- ✅ User can access admin panel

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review logs in `storage/logs/laravel.log`
3. Check Resend dashboard for delivery status
4. Refer to documentation files in `docs/` folder
5. Contact development team

---

**Last Updated**: April 22, 2026
**Status**: Ready for Configuration
