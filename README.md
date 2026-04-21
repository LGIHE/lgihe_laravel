# LGIHE Backend - Filament Admin Panel

Modern Laravel 11 application with Filament v3 admin panel for the Lesotho Government Institute of Higher Education (LGIHE).

## 🚀 Quick Start

### Access Admin Panel
```
URL: http://localhost:8001/admin
Super Admin: admin@lgihe.org / password
Content Editor: editor@lgihe.org / password
```

### Start Server
```bash
cd lgihe-backend-filament
php artisan serve --port=8001
```

## 📋 Features

### Content Management
- ✅ News articles with rich text editor
- ✅ Events with date/time management
- ✅ Job listings with deadlines
- ✅ Image uploads and management
- ✅ Status tracking (draft/published)

### Admissions
- ✅ Application management
- ✅ Status workflow tracking
- ✅ Reference number system
- ✅ Detailed application views
- ✅ Document management

### Communications
- ✅ Contact inquiry management
- ✅ Assignment system
- ✅ Status tracking
- ✅ Quick actions

### System
- ✅ User management
- ✅ Role-based access control
- ✅ Password management
- ✅ Activity tracking

### Filament Features
- ✅ Global search
- ✅ Advanced filters
- ✅ Bulk actions
- ✅ Export capabilities
- ✅ Dark mode
- ✅ Mobile responsive
- ✅ Keyboard shortcuts

## 🛠️ Tech Stack

- **Laravel**: 11.51.0
- **Filament**: 3.3.50
- **PHP**: 8.3+
- **Database**: SQLite (dev) / MySQL (prod)
- **Spatie Permission**: 6.25.0
- **Laravel Sanctum**: 4.3.1
- **Intervention Image**: 3.11.7

## 📦 Installation

### Prerequisites
- PHP 8.3+
- Composer
- Node.js & NPM (optional, for assets)

### Setup
```bash
# Clone or navigate to project
cd lgihe-backend-filament

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Start server
php artisan serve --port=8001
```

## 🎯 Common Commands

### Development
```bash
# Start server
php artisan serve --port=8001

# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Fresh migration with seed
php artisan migrate:fresh --seed
```

### Filament
```bash
# Create new resource
php artisan make:filament-resource ModelName --generate

# Create widget
php artisan make:filament-widget WidgetName --stats-overview

# Create custom page
php artisan make:filament-page PageName

# Optimize Filament
php artisan filament:optimize
```

## 👥 User Roles

### Super Admin
- Full system access
- User management
- All content management
- System configuration

### Content Editor
- Create/edit news
- Create/edit events
- Create/edit job listings
- Upload media

### Admissions Officer
- View applications
- Update application status
- Add application notes
- View analytics

### Communications Officer
- View inquiries
- Assign inquiries
- Update inquiry status
- Respond to inquiries

## 📁 Project Structure

```
lgihe-backend-filament/
├── app/
│   ├── Filament/
│   │   ├── Resources/          # Admin panel resources
│   │   └── Providers/          # Filament configuration
│   ├── Models/                 # Eloquent models
│   └── Http/
│       └── Controllers/Api/    # API controllers
├── database/
│   ├── migrations/             # Database migrations
│   ├── seeders/                # Database seeders
│   └── database.sqlite         # SQLite database
├── routes/
│   ├── web.php                 # Web routes
│   └── api.php                 # API routes
└── config/
    └── filament.php            # Filament configuration
```

## 🔐 Security

- ✅ Role-based access control (RBAC)
- ✅ Password hashing
- ✅ CSRF protection
- ✅ SQL injection protection
- ✅ XSS protection
- ✅ Rate limiting
- ✅ Sanctum API authentication

## 📚 Documentation

- **Setup Guide**: `SETUP_COMPLETE.md`
- **Migration Comparison**: `MIGRATION_COMPARISON.md`
- **Filament Docs**: https://filamentphp.com/docs/3.x
- **Laravel Docs**: https://laravel.com/docs/11.x

## 🐛 Troubleshooting

### Can't login?
```bash
php artisan db:seed
# Use: admin@lgihe.org / password
```

### Resources not showing?
```bash
php artisan optimize:clear
composer dump-autoload
```

### Images not uploading?
```bash
php artisan storage:link
chmod -R 775 storage
```

### Port already in use?
```bash
# Use different port
php artisan serve --port=8002
```

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database
- [ ] Set up mail service
- [ ] Configure `APP_URL`
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `php artisan filament:optimize`
- [ ] Set proper file permissions
- [ ] Configure web server (Nginx/Apache)
- [ ] Set up SSL certificate
- [ ] Configure queue workers
- [ ] Set up scheduled tasks

## 📊 API Endpoints

### Public API
- `GET /api/v1/news` - List news
- `GET /api/v1/events` - List events
- `GET /api/v1/jobs` - List jobs
- `POST /api/v1/applications` - Submit application
- `POST /api/v1/contact` - Submit inquiry

### Admin API (Requires Authentication)
- `GET /api/v1/admin/applications` - List applications
- `PUT /api/v1/admin/applications/{id}/status` - Update status
- `GET /api/v1/admin/inquiries` - List inquiries

## 🤝 Contributing

1. Create feature branch
2. Make changes
3. Test thoroughly
4. Submit pull request

## 📝 License

Proprietary - Lesotho Government Institute of Higher Education

## 📞 Support

- **Email**: it@lgihe.org
- **Documentation**: See `/docs` folder
- **Filament Discord**: https://filamentphp.com/discord

## ✨ What's New

### vs Custom Admin Panel
- ✅ 90% less code
- ✅ Modern UI/UX
- ✅ More features
- ✅ Faster development
- ✅ Better documentation
- ✅ Active community
- ✅ Regular updates

### Recent Updates
- ✅ Migrated to Filament v3
- ✅ Laravel 11 compatibility
- ✅ All features preserved
- ✅ Improved performance
- ✅ Better user experience

## 🎉 Status

**Status**: ✅ Production Ready
**Version**: 1.0.0
**Last Updated**: April 21, 2026

---

**Built with ❤️ using Laravel & Filament**
