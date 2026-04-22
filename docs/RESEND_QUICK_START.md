# Resend Email Integration - Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Step 1: Get Your Resend API Key (2 minutes)

1. Go to [resend.com](https://resend.com) and sign up
2. Click "API Keys" in the dashboard
3. Click "Create API Key"
4. Copy your API key (starts with `re_`)

### Step 2: Configure Your Application (1 minute)

Open your `.env` file and update these lines:

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_paste_your_key_here
MAIL_FROM_ADDRESS="noreply@lgihe.org"
MAIL_FROM_NAME="LGIHE Backend"
```

### Step 3: Test It (2 minutes)

#### Option A: Test with Log (No Resend needed)

1. Temporarily set in `.env`:
   ```env
   MAIL_MAILER=log
   QUEUE_CONNECTION=sync
   ```

2. Go to admin panel: `http://localhost:8001/admin`

3. Create a new user:
   - Fill in name and email
   - **Important:** Assign at least one role (e.g., "Viewer")
   - Leave password empty

4. Check `storage/logs/laravel.log` for the email

5. Copy the password setup URL and open it in your browser

6. Set a password and verify it works!

#### Option B: Test with Real Email

1. Make sure `.env` has:
   ```env
   MAIL_MAILER=resend
   RESEND_API_KEY=re_your_key
   QUEUE_CONNECTION=sync
   ```

2. Go to admin panel: `http://localhost:8001/admin`

3. Create a new user with YOUR email address:
   - Fill in name and your email
   - **Important:** Assign at least one role (e.g., "Viewer")
   - Leave password empty

4. Check your inbox for the email

5. Click the link and set your password

6. Verify you're logged in!

**⚠️ Important:** Always assign at least one role when creating users. Without a role, users can set their password but won't be able to access the admin panel (403 error). See `docs/USER_ROLES_PERMISSIONS.md` for details.

## ✅ That's It!

Your email integration is now working. Users will automatically receive password setup emails when created.

## 📋 What Happens When You Create a User?

1. Admin creates user (password optional)
2. Email sent automatically via Resend
3. User receives email with "Set Password" button
4. User clicks button → Opens backend password setup page
5. User sets password → Automatically logged in
6. User redirected to admin panel

## 🎨 Customize (Optional)

### Change Email Content
Edit: `app/Notifications/UserCreatedNotification.php`

### Change Password Setup Page Design
Edit: `resources/views/auth/password-setup.blade.php`

### Change Colors/Styling
Edit: `resources/views/layouts/auth.blade.php`

## 🔧 Production Setup

Before deploying to production:

1. **Verify Your Domain in Resend**
   - Add your domain in Resend dashboard
   - Add DNS records (SPF, DKIM)
   - Wait for verification (5-10 minutes)

2. **Update Production .env**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   MAIL_MAILER=resend
   RESEND_API_KEY=re_production_key
   MAIL_FROM_ADDRESS="noreply@lgihe.org"
   APP_URL=https://api.lgihe.ac.ug
   ```

3. **Set Up Queue Worker**
   ```bash
   php artisan queue:work --daemon
   ```
   
   Or use supervisor to keep it running.

## 🆘 Troubleshooting

### Emails Not Sending?
- Check `RESEND_API_KEY` is correct
- Verify domain in Resend dashboard
- Check `storage/logs/laravel.log` for errors

### Token Expired?
- Tokens expire after 60 minutes
- Admin can resend by editing and saving the user

### Can't Access Admin Panel After Setup (403 Error)?
**Problem:** User sets password successfully but gets 403 Forbidden error.

**Solution:**
- User needs at least one role assigned
- Edit the user in admin panel
- Assign a role (e.g., "Viewer", "Editor", "Admin")
- User can now access the admin panel
- See `docs/USER_ROLES_PERMISSIONS.md` for full details

### Queue Worker Questions?
- **Development:** Use `QUEUE_CONNECTION=sync` (no worker needed)
- **Production:** Use `QUEUE_CONNECTION=database` with Supervisor
- See `docs/QUEUE_SETUP.md` for complete guide

## 📚 Full Documentation

- **Complete Guide**: `docs/RESEND_INTEGRATION.md`
- **Implementation Details**: `docs/RESEND_IMPLEMENTATION_COMPLETE.md`
- **Setup Checklist**: `docs/RESEND_CHECKLIST.md`
- **Queue Setup**: `docs/QUEUE_SETUP.md` 🔄
- **User Roles & Permissions**: `docs/USER_ROLES_PERMISSIONS.md` 🔐

## 💡 Tips

- ✅ **Always assign roles** when creating users (prevents 403 errors)
- ✅ Use `MAIL_MAILER=log` for local development
- ✅ Use `QUEUE_CONNECTION=sync` for simple testing (no worker needed)
- ✅ Customize views to match your branding
- ✅ Monitor email delivery in Resend dashboard
- ✅ See `docs/QUEUE_SETUP.md` for production queue configuration

---

**Need Help?** Check the full documentation in the `docs/` folder or contact support.
