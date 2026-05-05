# Abuse Reporting System - Quick Start

## Overview

The Abuse Reporting System allows students, staff, and community members to report incidents of abuse, harassment, discrimination, and other safety concerns at LGIHE. The system supports both identified and anonymous reporting.

---

## 📋 Table of Contents

1. [API Endpoint](#api-endpoint)
2. [Quick Test](#quick-test)
3. [Admin Panel](#admin-panel)
4. [Configuration](#configuration)
5. [Testing](#testing)
6. [Documentation](#documentation)

---

## 🚀 API Endpoint

### Endpoint
```
POST /api/v1/report-abuse
```

### Request Example (Anonymous)
```json
{
  "anonymousReport": true,
  "incidentType": "bullying",
  "incidentDate": "2026-05-01",
  "incidentLocation": "Library",
  "personsInvolved": "Student A",
  "detailedDescription": "Description of the incident..."
}
```

### Request Example (Identified)
```json
{
  "reporterName": "John Doe",
  "reporterEmail": "john@example.com",
  "reporterPhone": "+256700000000",
  "reporterRelationship": "witness",
  "preferredContact": "email",
  "anonymousReport": false,
  "incidentType": "verbal-abuse",
  "incidentDate": "2026-05-01",
  "incidentLocation": "Classroom 101",
  "personsInvolved": "Staff Member X",
  "detailedDescription": "Detailed description...",
  "witnessesPresent": "Student B, Student C",
  "previouslyReported": "No",
  "evidenceAvailable": "Audio recording"
}
```

### Response (Success)
```json
{
  "success": true,
  "message": "Report submitted successfully...",
  "reportId": "ABR-1715234567890-ABC123XYZ"
}
```

---

## 🧪 Quick Test

### Using cURL
```bash
curl -X POST http://localhost:8000/api/v1/report-abuse \
  -H "Content-Type: application/json" \
  -d '{
    "anonymousReport": true,
    "incidentType": "bullying",
    "incidentDate": "2026-05-01",
    "incidentLocation": "Library",
    "personsInvolved": "Test Person",
    "detailedDescription": "This is a test report."
  }'
```

### Using PHP Artisan
```bash
# Run the test suite
php artisan test --filter=AbuseReportTest

# Seed test data
php artisan db:seed --class=AbuseReportSeeder
```

---

## 🎛️ Admin Panel

### Access
Navigate to: `/admin/abuse-reports`

### Features
- View all abuse reports
- Filter by incident type, status, anonymous/identified
- Update report status (pending → in progress → resolved)
- Assign reports to team members
- View full report details
- Soft delete reports

### Navigation Badge
The navigation menu shows a red badge with the count of pending reports.

---

## ⚙️ Configuration

### 1. Environment Variables

Add to `.env`:
```env
# Email Configuration
MAIL_MAILER=resend
RESEND_API_KEY=your_resend_api_key_here

# Email Addresses
MAIL_FROM_ADDRESS="noreply@lgihe.org"
MAIL_FROM_NAME="LGIHE Safeguarding"
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Start Queue Worker
```bash
php artisan queue:work
```

### 4. Verify Route
```bash
php artisan route:list --path=report-abuse
```

---

## 🧪 Testing

### Run Tests
```bash
# Run all abuse report tests
php artisan test --filter=AbuseReportTest

# Run specific test
php artisan test --filter=test_can_submit_anonymous_abuse_report
```

### Generate Test Data
```bash
# Seed 15 test reports
php artisan db:seed --class=AbuseReportSeeder

# Or use tinker
php artisan tinker
>>> AbuseReport::factory()->count(10)->create();
>>> AbuseReport::factory()->anonymous()->count(5)->create();
```

### Manual Testing
```bash
# Test anonymous report
curl -X POST http://localhost:8000/api/v1/report-abuse \
  -H "Content-Type: application/json" \
  -d @tests/fixtures/anonymous-report.json

# Test identified report
curl -X POST http://localhost:8000/api/v1/report-abuse \
  -H "Content-Type: application/json" \
  -d @tests/fixtures/identified-report.json
```

---

## 📚 Documentation

### Full Documentation
- **Frontend & Backend Overview**: `docs/abuse-reporting-system.md`
- **Backend Quick Start**: `docs/abuse-reporting-backend-quickstart.md`
- **Backend Implementation**: `docs/abuse-reporting-backend-implementation.md`

### Key Files

#### Backend
- **Migration**: `database/migrations/2026_05_05_071446_create_abuse_reports_table.php`
- **Model**: `app/Models/AbuseReport.php`
- **Controller**: `app/Http/Controllers/Api/V1/AbuseReportController.php`
- **Mail**: `app/Mail/AbuseReportSubmitted.php`
- **Email Template**: `resources/views/emails/abuse-report.blade.php`
- **Filament Resource**: `app/Filament/Resources/AbuseReportResource.php`
- **Routes**: `routes/api.php`

#### Testing
- **Tests**: `tests/Feature/AbuseReportTest.php`
- **Factory**: `database/factories/AbuseReportFactory.php`
- **Seeder**: `database/seeders/AbuseReportSeeder.php`

---

## 🔒 Security

### Important Notes
1. **HTTPS Only**: Use HTTPS in production
2. **Rate Limiting**: Consider adding rate limiting
3. **Access Control**: Restrict admin panel access
4. **Data Protection**: Reporter information is hidden by default
5. **Audit Trail**: Soft deletes maintain audit trail

### Recommended Rate Limiting
```php
// In routes/api.php
Route::middleware('throttle:10,1')->group(function () {
    Route::post('v1/report-abuse', [AbuseReportController::class, 'store']);
});
```

---

## 📧 Email Configuration

### Email Details
- **From**: `LGIHE Safeguarding <noreply@lgihe.org>`
- **To**: `safeguarding@lgihe.ac.ug`
- **Subject**: `🚨 URGENT: Abuse Report [REPORT-ID] - [INCIDENT-TYPE]`

### Email Service
The system uses **Resend** for email delivery. Ensure:
1. RESEND_API_KEY is set in `.env`
2. Sender domain is verified in Resend
3. Queue worker is running

### Test Email
```bash
php artisan tinker
>>> Mail::raw('Test', function($msg) { 
    $msg->to('safeguarding@lgihe.ac.ug')->subject('Test'); 
});
```

---

## 🐛 Troubleshooting

### Emails Not Sending
```bash
# Check queue worker
php artisan queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Database Issues
```bash
# Run migrations
php artisan migrate

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### Route Not Found
```bash
# Clear route cache
php artisan route:clear

# List routes
php artisan route:list --path=report-abuse
```

---

## 📊 Monitoring

### What to Monitor
1. **Email Delivery**: Check Resend dashboard
2. **Queue Jobs**: Monitor failed jobs
3. **API Response Times**: Track endpoint performance
4. **Error Rates**: Alert on increased errors
5. **Report Volume**: Track submission patterns

### Logs
```bash
# View logs
tail -f storage/logs/laravel.log

# View queue logs
php artisan queue:work --verbose
```

---

## 🔄 Maintenance

### Daily
- Check queue for failed jobs
- Monitor email delivery logs
- Review pending reports

### Weekly
- Review error logs
- Check database storage
- Verify email service status

### Monthly
- Test end-to-end flow
- Review incident types
- Check for security updates

---

## 📞 Support

For technical issues:
- **IT Support**: tech@lgihe.ac.ug
- **Safeguarding Team**: safeguarding@lgihe.ac.ug
- **Emergency**: (+256) 414 222 517

---

## 📝 Incident Types

Valid incident types:
- `physical-abuse` - Physical Abuse
- `sexual-harassment` - Sexual Harassment
- `sexual-assault` - Sexual Assault
- `verbal-abuse` - Verbal Abuse
- `bullying` - Bullying
- `discrimination` - Discrimination
- `stalking` - Stalking
- `emotional-abuse` - Emotional/Psychological Abuse
- `financial-exploitation` - Financial Exploitation
- `neglect` - Neglect
- `other` - Other

---

## 🎯 Quick Commands

```bash
# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed --class=AbuseReportSeeder

# Run tests
php artisan test --filter=AbuseReportTest

# Start queue worker
php artisan queue:work

# Check routes
php artisan route:list --path=report-abuse

# Clear caches
php artisan cache:clear
php artisan route:clear
php artisan config:clear
```

---

**Version**: 1.0.0  
**Last Updated**: May 5, 2026  
**Maintained By**: LGIHE IT Department
