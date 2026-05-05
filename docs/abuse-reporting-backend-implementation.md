# Abuse Reporting System - Backend Implementation

## Overview

This document describes the Laravel backend implementation for the LGIHE Abuse Reporting System. The backend provides a secure API endpoint for receiving abuse reports from the frontend, stores them in the database, and sends email notifications to the safeguarding team.

---

## Implementation Summary

### Components Created

1. **Database Migration**: `database/migrations/2026_05_05_071446_create_abuse_reports_table.php`
2. **Model**: `app/Models/AbuseReport.php`
3. **Controller**: `app/Http/Controllers/Api/V1/AbuseReportController.php`
4. **Mail Class**: `app/Mail/AbuseReportSubmitted.php`
5. **Email Template**: `resources/views/emails/abuse-report.blade.php`
6. **Filament Resource**: `app/Filament/Resources/AbuseReportResource.php`
7. **API Route**: Added to `routes/api.php`

---

## API Endpoint

### Endpoint Details

**URL**: `/api/v1/report-abuse`  
**Method**: `POST`  
**Content-Type**: `application/json`

### Request Body

#### Required Fields
```json
{
  "incidentType": "string",           // One of the valid incident types
  "incidentDate": "YYYY-MM-DD",       // Date of incident (not in future)
  "incidentLocation": "string",       // Where it happened (max 1000 chars)
  "personsInvolved": "string",        // Who was involved (max 5000 chars)
  "detailedDescription": "string"     // Full description (max 10000 chars)
}
```

#### Optional Fields (Reporter Information)
```json
{
  "reporterName": "string",           // Reporter's name (max 255 chars)
  "reporterEmail": "string",          // Valid email address
  "reporterPhone": "string",          // Phone number (max 50 chars)
  "reporterRelationship": "string",   // victim|witness|third-party|concerned-party|other
  "preferredContact": "string",       // email|phone|no-contact
  "anonymousReport": boolean          // true for anonymous reports
}
```

#### Optional Fields (Additional Details)
```json
{
  "witnessesPresent": "string",       // Witness information (max 5000 chars)
  "previouslyReported": "string",     // Prior reporting details (max 5000 chars)
  "evidenceAvailable": "string"       // Evidence description (max 5000 chars)
}
```

### Valid Incident Types

- `physical-abuse`
- `sexual-harassment`
- `sexual-assault`
- `verbal-abuse`
- `bullying`
- `discrimination`
- `stalking`
- `emotional-abuse`
- `financial-exploitation`
- `neglect`
- `other`

### Response Format

#### Success Response (201 Created)
```json
{
  "success": true,
  "message": "Report submitted successfully. The safeguarding team has been notified and will review your report.",
  "reportId": "ABR-1715234567890-ABC123XYZ"
}
```

#### Validation Error (422 Unprocessable Entity)
```json
{
  "success": false,
  "message": "Validation failed. Please check your input.",
  "errors": {
    "incidentType": ["The incident type field is required."],
    "incidentDate": ["The incident date must be a date before or equal to today."]
  }
}
```

#### Server Error (500 Internal Server Error)
```json
{
  "success": false,
  "message": "Failed to submit report. Please try again or contact support if the problem persists."
}
```

---

## Database Schema

### Table: `abuse_reports`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `report_id` | varchar(50) | No | Unique report identifier (e.g., ABR-1715234567890-ABC123XYZ) |
| `reporter_name` | varchar(255) | Yes | Reporter's name (null for anonymous) |
| `reporter_email` | varchar(255) | Yes | Reporter's email (null for anonymous) |
| `reporter_phone` | varchar(50) | Yes | Reporter's phone (null for anonymous) |
| `reporter_relationship` | varchar(50) | Yes | Relationship to incident |
| `preferred_contact` | varchar(20) | Yes | Preferred contact method |
| `anonymous_report` | boolean | No | Whether report is anonymous (default: false) |
| `incident_type` | varchar(50) | No | Type of abuse/incident |
| `incident_date` | date | No | Date of incident |
| `incident_location` | text | No | Location of incident |
| `persons_involved` | text | No | People involved in incident |
| `detailed_description` | text | No | Detailed description of incident |
| `witnesses_present` | text | Yes | Witness information |
| `previously_reported` | text | Yes | Prior reporting details |
| `evidence_available` | text | Yes | Evidence description |
| `status` | varchar(20) | No | Report status (default: 'pending') |
| `assigned_to` | bigint | Yes | Foreign key to users table |
| `resolved_at` | timestamp | Yes | When report was resolved |
| `created_at` | timestamp | No | When report was created |
| `updated_at` | timestamp | No | When report was last updated |
| `deleted_at` | timestamp | Yes | Soft delete timestamp |

### Indexes

- `report_id` (unique)
- `incident_type`
- `status`
- `created_at`

---

## Model Features

### AbuseReport Model

**Location**: `app/Models/AbuseReport.php`

#### Key Features

1. **Soft Deletes**: Reports are soft-deleted for audit trail
2. **Hidden Fields**: Reporter information is hidden from JSON serialization by default
3. **Date Casting**: Proper date/datetime casting for date fields
4. **Relationships**: Belongs to User (assigned_to)
5. **Scopes**: 
   - `pending()` - Get pending reports
   - `inProgress()` - Get in-progress reports
   - `resolved()` - Get resolved reports
   - `anonymous()` - Get anonymous reports

#### Helper Methods

```php
// Generate unique report ID
AbuseReport::generateReportId()
// Returns: "ABR-1715234567890-ABC123XYZ"

// Get incident type display name
$report->incident_type_display
// Returns: "Sexual Harassment" (formatted)
```

---

## Email Notification

### Mail Class

**Location**: `app/Mail/AbuseReportSubmitted.php`

#### Features

- Implements `ShouldQueue` for asynchronous sending
- Configurable reply-to address (reporter's email or safeguarding email)
- Urgent subject line with report ID and incident type
- Passes report data to email view

### Email Template

**Location**: `resources/views/emails/abuse-report.blade.php`

#### Email Structure

1. **Header**: Red banner with "CONFIDENTIAL ABUSE REPORT"
2. **Report ID Banner**: Yellow banner with report details
3. **Anonymous Notice**: Displayed for anonymous reports
4. **Reporter Information**: Contact details (if not anonymous)
5. **Incident Details**: Type, date, location
6. **Persons Involved**: Names/descriptions
7. **Detailed Description**: Full incident narrative
8. **Witnesses**: Witness information (if provided)
9. **Previous Reports**: Prior reporting history (if provided)
10. **Evidence**: Available evidence description (if provided)
11. **Action Required**: Checklist for safeguarding team
12. **Confidentiality Notice**: Legal and policy reminders
13. **Footer**: Contact information

#### Email Recipients

- **To**: `safeguarding@lgihe.ac.ug`
- **From**: `LGIHE Safeguarding <noreply@lgihe.ac.ug>`
- **Reply-To**: Reporter's email (if provided) or safeguarding email

---

## Admin Panel (Filament)

### Abuse Report Resource

**Location**: `app/Filament/Resources/AbuseReportResource.php`

#### Features

1. **Navigation**:
   - Icon: Shield with exclamation mark
   - Group: "Safeguarding"
   - Badge: Shows count of pending reports
   - Badge Color: Red (danger)

2. **Table View**:
   - Columns: Report ID, Type, Incident Date, Anonymous, Status, Assigned To, Submitted
   - Sortable and searchable
   - Color-coded badges for incident types and status
   - Icons for anonymous vs identified reports

3. **Filters**:
   - Incident Type
   - Status
   - Anonymous Reports (ternary filter)
   - Date Range (submitted from/until)
   - Trashed (soft-deleted reports)

4. **Actions**:
   - View: View full report details
   - Edit: Update status and assignment
   - Delete: Soft delete report
   - Restore: Restore soft-deleted report
   - Force Delete: Permanently delete report

5. **Form Sections**:
   - **Report Information**: Status, assigned user, resolved date (editable)
   - **Incident Details**: All incident information (read-only)
   - **Additional Information**: Witnesses, previous reports, evidence (read-only)
   - **Reporter Information**: Contact details (read-only, collapsible)

6. **Security**:
   - Reporter information is disabled (read-only) to prevent tampering
   - Incident details are disabled to maintain report integrity
   - Only status, assignment, and resolution date can be edited

#### Status Options

- `pending` - New report awaiting review
- `in_progress` - Report is being investigated
- `resolved` - Investigation completed
- `closed` - Report closed (no further action)

---

## Configuration

### Environment Variables

Add to `.env` file:

```env
# Email Configuration
MAIL_MAILER=resend
RESEND_API_KEY=your_resend_api_key_here

# Email Addresses
MAIL_FROM_ADDRESS="noreply@lgihe.ac.ug"
MAIL_FROM_NAME="LGIHE Safeguarding"
```

### Queue Configuration

The email notification is queued for asynchronous processing. Ensure your queue worker is running:

```bash
php artisan queue:work
```

For production, use a process manager like Supervisor to keep the queue worker running.

---

## Testing

### Manual Testing with cURL

#### Test Anonymous Report

```bash
curl -X POST http://localhost:8000/api/v1/report-abuse \
  -H "Content-Type: application/json" \
  -d '{
    "anonymousReport": true,
    "incidentType": "bullying",
    "incidentDate": "2026-05-01",
    "incidentLocation": "Library",
    "personsInvolved": "Test Person",
    "detailedDescription": "This is a test report for anonymous submission."
  }'
```

#### Test Identified Report

```bash
curl -X POST http://localhost:8000/api/v1/report-abuse \
  -H "Content-Type: application/json" \
  -d '{
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
    "detailedDescription": "Detailed test description of the incident.",
    "witnessesPresent": "Student B, Student C",
    "previouslyReported": "No",
    "evidenceAvailable": "Audio recording"
  }'
```

#### Test Validation Error

```bash
curl -X POST http://localhost:8000/api/v1/report-abuse \
  -H "Content-Type: application/json" \
  -d '{
    "incidentType": "bullying"
  }'
```

### Testing Checklist

- [ ] API accepts valid anonymous reports
- [ ] API accepts valid identified reports
- [ ] API rejects requests with missing required fields
- [ ] API rejects invalid incident types
- [ ] API rejects future incident dates
- [ ] Report ID is generated correctly
- [ ] Report is saved to database
- [ ] Email is sent to safeguarding team
- [ ] Email contains all submitted information
- [ ] Anonymous reports don't include reporter info in email
- [ ] Identified reports include reporter info in email
- [ ] Queue job processes successfully
- [ ] Admin panel displays reports correctly
- [ ] Admin can filter and search reports
- [ ] Admin can update status and assignment
- [ ] Admin cannot edit incident details
- [ ] Soft delete works correctly
- [ ] Navigation badge shows pending count

---

## Security Considerations

### Data Protection

1. **HTTPS Only**: Ensure API is only accessible via HTTPS in production
2. **Input Validation**: All inputs are validated before processing
3. **SQL Injection**: Using Eloquent ORM prevents SQL injection
4. **XSS Protection**: Laravel's Blade templating escapes output by default
5. **CSRF Protection**: API routes use Sanctum for authentication (if needed)
6. **Rate Limiting**: Consider adding rate limiting to prevent abuse

### Privacy

1. **Reporter Information**: Hidden from JSON serialization by default
2. **Soft Deletes**: Reports are soft-deleted for audit trail
3. **Access Control**: Only authorized users can access admin panel
4. **Email Security**: Emails marked as confidential
5. **Logging**: Sensitive information excluded from logs

### Recommended Additional Security

```php
// Add to routes/api.php
Route::middleware('throttle:10,1')->group(function () {
    Route::post('v1/report-abuse', [AbuseReportController::class, 'store']);
});
```

This limits abuse report submissions to 10 per minute per IP address.

---

## Monitoring and Logging

### What is Logged

The controller logs the following information (without sensitive details):

```php
Log::info('Abuse report submitted', [
    'report_id' => $reportId,
    'incident_type' => $request->input('incidentType'),
    'is_anonymous' => $isAnonymous,
    'timestamp' => now()->toIso8601String(),
]);
```

### What is NOT Logged

- Reporter's personal information
- Detailed incident descriptions
- Names of persons involved
- Any sensitive content from the report

### Error Logging

Errors are logged without sensitive information:

```php
Log::error('Failed to submit abuse report', [
    'error' => $e->getMessage(),
    'timestamp' => now()->toIso8601String(),
]);
```

### Monitoring Recommendations

1. **Email Delivery**: Monitor Resend dashboard for delivery status
2. **Queue Jobs**: Monitor failed jobs table
3. **API Response Times**: Track endpoint performance
4. **Error Rates**: Alert on increased error rates
5. **Report Volume**: Track submission patterns

---

## Maintenance

### Regular Tasks

#### Daily
- Check queue for failed jobs
- Monitor email delivery logs
- Review pending reports in admin panel

#### Weekly
- Review error logs
- Check database storage usage
- Verify email service status

#### Monthly
- Test end-to-end submission flow
- Review and update incident type options if needed
- Check for security updates

#### Quarterly
- Review access control policies
- Update email template if needed
- Audit soft-deleted reports

### Database Maintenance

```bash
# Clear soft-deleted reports older than 1 year
php artisan tinker
>>> AbuseReport::onlyTrashed()->where('deleted_at', '<', now()->subYear())->forceDelete();
```

---

## Troubleshooting

### Issue: Emails not being sent

**Possible Causes**:
1. RESEND_API_KEY not set or invalid
2. Queue worker not running
3. Email address not verified in Resend
4. Network connectivity issues

**Solutions**:
```bash
# Check queue worker status
php artisan queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Test email configuration
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

### Issue: Validation errors

**Possible Causes**:
1. Missing required fields
2. Invalid field values
3. Date format incorrect
4. Field length exceeded

**Solutions**:
- Check request body matches expected format
- Verify incident type is one of the valid options
- Ensure date is in YYYY-MM-DD format
- Check field length limits

### Issue: Database errors

**Possible Causes**:
1. Migration not run
2. Database connection issues
3. Duplicate report_id (rare)

**Solutions**:
```bash
# Run migrations
php artisan migrate

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check table exists
>>> Schema::hasTable('abuse_reports');
```

### Issue: Admin panel not showing reports

**Possible Causes**:
1. User doesn't have permission
2. Reports are soft-deleted
3. Filters are active

**Solutions**:
- Check user roles and permissions
- Clear filters in admin panel
- Check "Trashed" filter to see soft-deleted reports

---

## API Integration Examples

### JavaScript (Fetch API)

```javascript
async function submitAbuseReport(formData) {
  try {
    const response = await fetch('/api/v1/report-abuse', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    });

    const data = await response.json();

    if (data.success) {
      console.log('Report submitted:', data.reportId);
      return data;
    } else {
      console.error('Submission failed:', data.message);
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error submitting report:', error);
    throw error;
  }
}

// Usage
const reportData = {
  anonymousReport: true,
  incidentType: 'bullying',
  incidentDate: '2026-05-01',
  incidentLocation: 'Library',
  personsInvolved: 'Student A',
  detailedDescription: 'Description of the incident...',
};

submitAbuseReport(reportData)
  .then(result => {
    alert(`Report submitted successfully! Report ID: ${result.reportId}`);
  })
  .catch(error => {
    alert('Failed to submit report. Please try again.');
  });
```

### React Example

```jsx
import { useState } from 'react';

function AbuseReportForm() {
  const [formData, setFormData] = useState({
    anonymousReport: false,
    incidentType: '',
    incidentDate: '',
    incidentLocation: '',
    personsInvolved: '',
    detailedDescription: '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setSuccess(null);

    try {
      const response = await fetch('/api/v1/report-abuse', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.success) {
        setSuccess(`Report submitted successfully! Report ID: ${data.reportId}`);
        // Reset form
        setFormData({
          anonymousReport: false,
          incidentType: '',
          incidentDate: '',
          incidentLocation: '',
          personsInvolved: '',
          detailedDescription: '',
        });
      } else {
        setError(data.message);
      }
    } catch (err) {
      setError('Failed to submit report. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Form fields here */}
      <button type="submit" disabled={loading}>
        {loading ? 'Submitting...' : 'Submit Report'}
      </button>
      {error && <div className="error">{error}</div>}
      {success && <div className="success">{success}</div>}
    </form>
  );
}
```

---

## Future Enhancements

### Potential Features

1. **SMS Notifications**: Send SMS alerts for urgent reports
2. **Case Management**: Add notes, attachments, and status updates
3. **Workflow Automation**: Auto-assign based on incident type
4. **Reporting Dashboard**: Analytics and statistics
5. **Multi-language Support**: Translate emails and admin panel
6. **File Uploads**: Allow evidence file uploads (with security measures)
7. **Anonymous Follow-up**: Secure messaging for anonymous reporters
8. **Integration**: Connect with external case management systems
9. **Audit Trail**: Track all actions taken on reports
10. **Escalation Rules**: Auto-escalate based on severity and time

---

## Support and Contact

For technical issues or questions:

- **IT Support**: tech@lgihe.ac.ug
- **Safeguarding Team**: safeguarding@lgihe.ac.ug
- **Emergency**: (+256) 414 222 517

---

## Changelog

### Version 1.0.0 (May 5, 2026)
- Initial backend implementation
- Database schema and migrations
- API endpoint for report submission
- Email notification system
- Filament admin panel resource
- Comprehensive documentation

---

## License

This implementation is confidential and intended for LGIHE use only.

**Document Version**: 1.0.0  
**Last Updated**: May 5, 2026  
**Maintained By**: LGIHE IT Department
