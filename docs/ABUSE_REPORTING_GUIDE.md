# 🛡️ Abuse Reporting System Guide

Complete guide for the LGIHE Abuse Reporting System.

## 🎯 Overview

The Abuse Reporting System allows students, staff, and community members to report incidents of abuse, harassment, or misconduct safely and confidentially.

### Key Features

- ✅ Anonymous or identified reporting
- ✅ Secure file uploads (evidence)
- ✅ Email notifications to safeguarding team
- ✅ Status tracking (pending, in progress, resolved)
- ✅ Assignment to investigators
- ✅ Confidential notes system
- ✅ Audit trail with soft deletes

## 📋 Report Types

- **Harassment**: Bullying, intimidation, unwanted contact
- **Discrimination**: Based on race, gender, religion, etc.
- **Sexual Misconduct**: Assault, harassment, inappropriate behavior
- **Physical Abuse**: Violence, threats, physical harm
- **Emotional Abuse**: Psychological harm, manipulation
- **Academic Misconduct**: Cheating, plagiarism, grade manipulation
- **Financial Misconduct**: Theft, fraud, misuse of funds
- **Other**: Any other concerns

## 🚀 Quick Start

### For Administrators

1. **Access Reports**: Navigate to **Safeguarding > Abuse Reports**
2. **View Details**: Click on any report to see full details
3. **Assign Report**: Select an investigator from the dropdown
4. **Update Status**: Change status as investigation progresses
5. **Add Notes**: Document actions taken (internal only)

### For API Integration

```javascript
// Submit abuse report
POST /api/v1/abuse-reports

{
  "incident_type": "harassment",
  "incident_date": "2026-05-01",
  "incident_time": "14:30",
  "location": "Library, 2nd Floor",
  "description": "Detailed description of incident...",
  "reporter_name": "John Doe", // Optional for anonymous
  "reporter_email": "john@example.com", // Optional
  "reporter_phone": "+266 1234 5678", // Optional
  "is_anonymous": false,
  "witness_present": true,
  "witness_details": "Jane Smith was present",
  "evidence_files": [file1, file2] // Optional
}
```

## 📊 Admin Panel Features

### View Reports

**Location**: `/admin/abuse-reports`

**Features**:
- List all reports with key information
- Filter by status, type, date range
- Search by report ID, reporter name, or description
- Sort by date, status, or type
- Bulk actions (assign, update status)

### Report Details

**Information Displayed**:
- Report ID and submission date
- Incident type and date/time
- Location and description
- Reporter information (if not anonymous)
- Witness details
- Evidence files (downloadable)
- Current status and assigned investigator
- Investigation notes
- Timeline of status changes

### Status Workflow

1. **Pending** (Initial state)
   - Report submitted
   - Email sent to safeguarding team
   - Awaiting assignment

2. **In Progress**
   - Assigned to investigator
   - Investigation underway
   - Notes being added

3. **Resolved**
   - Investigation complete
   - Actions taken
   - Case closed

### Actions Available

- **Assign**: Assign report to an investigator
- **Update Status**: Change report status
- **Add Note**: Add internal investigation notes
- **Download Evidence**: Download uploaded files
- **View Timeline**: See all status changes
- **Delete**: Soft delete (admin only)

## 🔒 Security & Privacy

### Anonymous Reporting

- Reporter identity not stored
- No email or phone required
- IP address not logged
- Cannot be traced back to reporter

### Data Protection

- All reports encrypted in database
- Evidence files stored securely
- Access restricted to authorized personnel
- Audit trail for all actions
- Soft deletes preserve data for legal purposes

### Permissions Required

- **View Reports**: `view_abuse_reports`
- **Create Reports**: Public (no auth required)
- **Update Reports**: `update_abuse_reports`
- **Delete Reports**: `delete_abuse_reports`

## 📧 Email Notifications

### Automatic Notifications

**When Report Submitted**:
- **To**: safeguarding@lgihe.ac.ug
- **Subject**: New Abuse Report - [Report ID]
- **Contains**: Report summary and link to admin panel

**Configuration**:
```env
MAIL_MAILER=resend
RESEND_API_KEY=your_api_key
MAIL_FROM_ADDRESS=noreply@lgihe.ac.ug
MAIL_FROM_NAME="LGIHE Safeguarding"
```

### Email Template

The notification includes:
- Report ID and submission time
- Incident type and date
- Location
- Brief description
- Reporter information (if not anonymous)
- Direct link to view report in admin panel

## 💻 API Reference

### Submit Report

```http
POST /api/v1/abuse-reports
Content-Type: multipart/form-data

Required Fields:
- incident_type (string)
- incident_date (date)
- location (string)
- description (text)

Optional Fields:
- incident_time (time)
- reporter_name (string)
- reporter_email (email)
- reporter_phone (string)
- is_anonymous (boolean, default: false)
- witness_present (boolean)
- witness_details (text)
- evidence_files[] (files)
```

**Response**:
```json
{
  "success": true,
  "message": "Report submitted successfully",
  "report": {
    "id": 123,
    "report_id": "ABR-2026-000123",
    "status": "pending"
  }
}
```

### List Reports (Admin)

```http
GET /api/v1/admin/abuse-reports
Authorization: Bearer {token}

Query Parameters:
- status (pending|in_progress|resolved)
- incident_type (harassment|discrimination|...)
- from_date (date)
- to_date (date)
- page (integer)
```

### Get Report Details (Admin)

```http
GET /api/v1/admin/abuse-reports/{id}
Authorization: Bearer {token}
```

### Update Report Status (Admin)

```http
PUT /api/v1/admin/abuse-reports/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "in_progress",
  "assigned_to": 5,
  "notes": "Investigation started"
}
```

## 🧪 Testing

### Manual Testing

1. **Submit Anonymous Report**:
   - Go to public reporting form
   - Fill in incident details
   - Leave reporter fields empty
   - Check "Submit Anonymously"
   - Submit

2. **Submit Identified Report**:
   - Fill in all fields including reporter info
   - Upload evidence files
   - Submit

3. **Admin Actions**:
   - Login to admin panel
   - View report in list
   - Open report details
   - Assign to investigator
   - Update status
   - Add notes

### Automated Testing

```bash
# Run abuse reporting tests
php artisan test --filter=AbuseReportTest

# Test email notifications
php artisan tinker
>>> $report = AbuseReport::factory()->create();
>>> Mail::to('safeguarding@lgihe.ac.ug')->send(new AbuseReportSubmitted($report));
```

## 🔧 Troubleshooting

### Emails Not Sending

**Problem**: No notification received after report submission

**Solution**:
```bash
# Check mail configuration
php artisan tinker
>>> config('mail.mailer')
>>> config('mail.from.address')

# Test email
>>> Mail::raw('Test', function($msg) {
>>>     $msg->to('safeguarding@lgihe.ac.ug')->subject('Test');
>>> });

# Check logs
tail -f storage/logs/laravel.log
```

### File Upload Fails

**Problem**: Evidence files not uploading

**Solution**:
```bash
# Check storage link
php artisan storage:link

# Check permissions
chmod -R 775 storage/app/public

# Check upload limits in php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Reports Not Showing

**Problem**: Reports submitted but not visible in admin panel

**Solution**:
```bash
# Check database
php artisan tinker
>>> AbuseReport::count()
>>> AbuseReport::latest()->first()

# Clear cache
php artisan optimize:clear

# Check permissions
>>> auth()->user()->can('view_abuse_reports')
```

## 📋 Best Practices

### For Administrators

1. **Respond Promptly**: Check reports daily
2. **Assign Quickly**: Assign reports within 24 hours
3. **Document Actions**: Add detailed notes
4. **Maintain Confidentiality**: Never discuss reports publicly
5. **Follow Up**: Update status as investigation progresses
6. **Preserve Evidence**: Download and backup evidence files

### For Developers

1. **Validate Input**: Always validate report data
2. **Sanitize Files**: Check file types and sizes
3. **Log Actions**: Log all status changes
4. **Test Emails**: Verify email delivery in staging
5. **Monitor Storage**: Check disk space for uploads
6. **Backup Data**: Regular database backups

## 📞 Support Contacts

- **Safeguarding Team**: safeguarding@lgihe.ac.ug
- **IT Support**: it@lgihe.ac.ug
- **Emergency**: +266 [Emergency Number]

## 📚 Additional Resources

- [Safeguarding Policy](https://lgihe.ac.ug/policies/safeguarding)
- [Reporting Guidelines](https://lgihe.ac.ug/report-abuse)
- [Support Services](https://lgihe.ac.ug/support)

---

**Version**: 1.0.0  
**Last Updated**: May 5, 2026  
**Status**: Production Ready ✅
