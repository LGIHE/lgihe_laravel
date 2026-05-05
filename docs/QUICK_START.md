# 🚀 Quick Start Guide

Get up and running with the LGIHE Backend system in minutes.

## 📋 Prerequisites

- PHP 8.3+
- Composer
- MySQL or SQLite
- Git

## ⚡ Installation

### 1. Clone & Setup

```bash
# Navigate to project
cd lgihe-backend-filament

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Configure Database

**Option A: SQLite (Development)**
```bash
# Already configured in .env
touch database/database.sqlite
```

**Option B: MySQL (Production)**
```env
# Edit .env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=lgihe_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Run Migrations & Seed

```bash
# Create database tables
php artisan migrate

# Seed with sample data
php artisan db:seed

# Create super admin
php artisan make:super-admin
```

### 4. Configure Email (Optional)

```env
# Add to .env
MAIL_MAILER=resend
RESEND_API_KEY=your_resend_api_key
MAIL_FROM_ADDRESS=noreply@lgihe.ac.ug
MAIL_FROM_NAME="LGIHE"
```

### 5. Start Server

```bash
php artisan serve --port=8001
```

Visit: **http://localhost:8001/admin**

## 🔑 Default Credentials

```
Super Admin:
Email: admin@lgihe.ac.ug
Password: password

Content Editor:
Email: editor@lgihe.ac.ug
Password: password
```

⚠️ **Change these passwords in production!**

## 📚 Common Tasks

### Create New User

```bash
# Via command line
php artisan tinker
>>> $user = User::create([
>>>     'name' => 'John Doe',
>>>     'email' => 'john@lgihe.ac.ug',
>>>     'password' => bcrypt('password')
>>> ]);
>>> $user->assignRole('Editor');
```

Or use the admin panel: **User Management > Users > New User**

### Add News Article

1. Login to admin panel
2. Navigate to **Content > News**
3. Click **New News**
4. Fill in details:
   - Title
   - Slug (auto-generated)
   - Excerpt
   - Content (rich text editor)
   - Featured image
   - Category
   - Status (draft/published)
5. Click **Create**

### Manage Applications

1. Navigate to **Admissions > Applications**
2. View submitted applications
3. Click on application to view details
4. Update status: Pending → Under Review → Accepted/Rejected
5. Add notes for internal tracking

### Handle Contact Inquiries

1. Navigate to **Communications > Contact Inquiries**
2. View new inquiries
3. Assign to team member
4. Update status: New → In Progress → Resolved
5. Add response notes

## 🔧 Useful Commands

### Development

```bash
# Start development server
php artisan serve --port=8001

# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Fresh database with seed data
php artisan migrate:fresh --seed

# Create storage link
php artisan storage:link
```

### Filament

```bash
# Create new resource
php artisan make:filament-resource ModelName --generate

# Create widget
php artisan make:filament-widget WidgetName

# Optimize Filament
php artisan filament:optimize

# Clear Filament cache
php artisan filament:cache-clear
```

### RBAC

```bash
# Create super admin
php artisan make:super-admin

# Seed permissions
php artisan db:seed --class=PermissionSeeder

# Clear permission cache
php artisan permission:cache-reset
```

### Queue & Jobs

```bash
# Run queue worker
php artisan queue:work

# Process failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

## 🎯 Next Steps

### For Administrators

1. **Change Default Passwords**
   - Login with default credentials
   - Go to User Profile
   - Change password

2. **Create Team Accounts**
   - Navigate to User Management > Users
   - Create accounts for team members
   - Assign appropriate roles

3. **Configure Email**
   - Set up Resend API key
   - Test email delivery
   - Configure notification preferences

4. **Add Content**
   - Create news articles
   - Add events
   - Post job listings
   - Upload media

### For Developers

1. **Read Documentation**
   - API_DOCUMENTATION.md - API endpoints
   - RBAC_GUIDE.md - User permissions
   - DEPLOYMENT.md - Production deployment

2. **Explore Codebase**
   - `app/Filament/Resources/` - Admin resources
   - `app/Http/Controllers/Api/` - API controllers
   - `app/Models/` - Database models
   - `routes/api.php` - API routes

3. **Set Up Development Environment**
   - Install IDE extensions
   - Configure debugging
   - Set up testing environment

4. **Run Tests**
   ```bash
   php artisan test
   ```

## 🔍 Troubleshooting

### Can't Login

```bash
# Reset database and create admin
php artisan migrate:fresh --seed
php artisan make:super-admin
```

### Resources Not Showing

```bash
# Clear caches
php artisan optimize:clear
composer dump-autoload
```

### Images Not Uploading

```bash
# Create storage link
php artisan storage:link

# Fix permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Port Already in Use

```bash
# Use different port
php artisan serve --port=8002
```

### Database Connection Error

```bash
# Check .env configuration
# For SQLite, ensure file exists:
touch database/database.sqlite

# For MySQL, verify credentials
php artisan tinker
>>> DB::connection()->getPdo();
```

## 📖 Learn More

- **API Documentation**: See `docs/API_DOCUMENTATION.md`
- **RBAC System**: See `docs/RBAC_GUIDE.md`
- **Analytics**: See `docs/ANALYTICS_GUIDE.md`
- **Abuse Reporting**: See `docs/ABUSE_REPORTING_GUIDE.md`
- **Deployment**: See `docs/DEPLOYMENT.md`

## 🆘 Getting Help

- **Check Logs**: `storage/logs/laravel.log`
- **Laravel Docs**: https://laravel.com/docs
- **Filament Docs**: https://filamentphp.com/docs
- **Email Support**: it@lgihe.ac.ug

## ✅ Checklist

- [ ] Dependencies installed
- [ ] Environment configured
- [ ] Database migrated
- [ ] Super admin created
- [ ] Server running
- [ ] Can access admin panel
- [ ] Can login successfully
- [ ] Email configured (optional)
- [ ] Storage link created
- [ ] Permissions working

---

**Ready to go!** 🎉

Visit http://localhost:8001/admin and start managing your content.

---

**Version**: 1.0.0  
**Last Updated**: May 5, 2026
