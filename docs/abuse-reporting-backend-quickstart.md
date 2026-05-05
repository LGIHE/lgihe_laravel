# Abuse Reporting System - Backend Quick Start Guide

## Quick Reference for Backend Implementation

This is a condensed guide for implementing the backend processing of abuse reports. For full documentation, see `abuse-reporting-system.md`.

---

## API Endpoint

**URL**: `/api/report-abuse`  
**Method**: `POST`  
**Content-Type**: `application/json`

---

## Request Body Fields

### Required Fields (Always)
```json
{
  "incidentType": "string",           // Type of abuse (see options below)
  "incidentDate": "YYYY-MM-DD",       // Date of incident
  "incidentLocation": "string",       // Where it happened
  "personsInvolved": "string",        // Who was involved
  "detailedDescription": "string"     // Full description
}
```

### Optional Fields (Reporter Information)
```json
{
  "reporterName": "string",           // Reporter's name
  "reporterEmail": "string",          // Reporter's email
  "reporterPhone": "string",          // Reporter's phone
  "reporterRelationship": "string",   // Relationship to incident
  "preferredContact": "string",       // How to contact (email/phone/no-contact)
  "anonymousReport": boolean          // Is this anonymous?
}
```

### Optional Fields (Additional Details)
```json
{
  "witnessesPresent": "string",       // Witness information
  "previouslyReported": "string",     // Prior reporting details
  "evidenceAvailable": "string"       // Evidence description
}
```

---

## Incident Type Options

| Value | Display Name |
|-------|--------------|
| `physical-abuse` | Physical Abuse |
| `sexual-harassment` | Sexual Harassment |
| `sexual-assault` | Sexual Assault |
| `verbal-abuse` | Verbal Abuse |
| `bullying` | Bullying |
| `discrimination` | Discrimination |
| `stalking` | Stalking |
| `emotional-abuse` | Emotional/Psychological Abuse |
| `financial-exploitation` | Financial Exploitation |
| `neglect` | Neglect |
| `other` | Other |

---

## Response Format

### Success (200 OK)
```json
{
  "success": true,
  "message": "Report submitted successfully",
  "reportId": "ABR-1715234567890-ABC123XYZ"
}
```

### Error (400 Bad Request)
```json
{
  "success": false,
  "message": "Required fields are missing"
}
```

### Error (500 Internal Server Error)
```json
{
  "success": false,
  "message": "Failed to submit report"
}
```

---

## Report ID Format

```
ABR-[TIMESTAMP]-[RANDOM]
```

Example: `ABR-1715234567890-ABC123XYZ`

- **ABR**: Abuse Report prefix
- **TIMESTAMP**: Unix timestamp (milliseconds)
- **RANDOM**: 9-character alphanumeric string (uppercase)

### Generation Code
```typescript
const reportId = `ABR-${Date.now()}-${Math.random().toString(36).substr(2, 9).toUpperCase()}`;
```

---

## Email Configuration

### Email Details
- **From**: `LGIHE Safeguarding <noreply@lgihe.org>`
- **To**: `safeguarding@lgihe.ac.ug`
- **Reply-To**: Reporter's email (if provided) or `safeguarding@lgihe.ac.ug`
- **Subject**: `🚨 URGENT: Abuse Report [REPORT-ID] - [INCIDENT-TYPE]`

### Example Subject
```
🚨 URGENT: Abuse Report [ABR-1715234567890-ABC123XYZ] - sexual-harassment
```

---

## Environment Variables Required

```env
RESEND_API_KEY=your_resend_api_key_here
```

---

## Backend Processing Steps

1. **Receive POST request** with JSON body
2. **Validate required fields**:
   - `incidentType`
   - `incidentDate`
   - `incidentLocation`
   - `personsInvolved`
   - `detailedDescription`
3. **Generate unique Report ID**
4. **Determine if anonymous** (check `anonymousReport` or missing contact info)
5. **Build email HTML** with all report details
6. **Send email** to safeguarding team
7. **Log submission** (without sensitive details)
8. **Return success response** with Report ID

---

## Database Schema (Optional)

If you want to store reports in a database:

```sql
CREATE TABLE abuse_reports (
  id VARCHAR(255) PRIMARY KEY,
  report_id VARCHAR(50) UNIQUE NOT NULL,
  reporter_name VARCHAR(255),
  reporter_email VARCHAR(255),
  reporter_phone VARCHAR(50),
  reporter_relationship VARCHAR(50),
  incident_type VARCHAR(50) NOT NULL,
  incident_date DATE NOT NULL,
  incident_location TEXT NOT NULL,
  persons_involved TEXT NOT NULL,
  detailed_description TEXT NOT NULL,
  witnesses_present TEXT,
  previously_reported TEXT,
  evidence_available TEXT,
  preferred_contact VARCHAR(20),
  anonymous_report BOOLEAN DEFAULT FALSE,
  status VARCHAR(20) DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Index for quick lookups
CREATE INDEX idx_report_id ON abuse_reports(report_id);
CREATE INDEX idx_incident_type ON abuse_reports(incident_type);
CREATE INDEX idx_status ON abuse_reports(status);
CREATE INDEX idx_created_at ON abuse_reports(created_at);
```

---

## Security Checklist

- [ ] Use HTTPS only
- [ ] Validate all input fields
- [ ] Sanitize data before storing/emailing
- [ ] Rate limit the API endpoint
- [ ] Log submissions (without sensitive data)
- [ ] Encrypt data at rest (if storing in database)
- [ ] Restrict access to safeguarding email
- [ ] Set up email delivery monitoring
- [ ] Implement CORS if needed
- [ ] Use environment variables for secrets

---

## Testing Commands

### Test with cURL
```bash
curl -X POST http://localhost:3000/api/report-abuse \
  -H "Content-Type: application/json" \
  -d '{
    "anonymousReport": true,
    "incidentType": "bullying",
    "incidentDate": "2026-05-01",
    "incidentLocation": "Library",
    "personsInvolved": "Test Person",
    "detailedDescription": "This is a test report"
  }'
```

### Test with JavaScript
```javascript
fetch('/api/report-abuse', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    anonymousReport: true,
    incidentType: 'bullying',
    incidentDate: '2026-05-01',
    incidentLocation: 'Library',
    personsInvolved: 'Test Person',
    detailedDescription: 'This is a test report'
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

---

## Email Service Alternatives

### Current: Resend
```typescript
import { resend } from '@/lib/resend';

await resend.emails.send({
  from: 'LGIHE Safeguarding <noreply@lgihe.org>',
  to: 'safeguarding@lgihe.ac.ug',
  subject: `🚨 URGENT: Abuse Report [${reportId}]`,
  html: emailHtml,
});
```

### Alternative: Nodemailer
```typescript
import nodemailer from 'nodemailer';

const transporter = nodemailer.createTransport({
  host: process.env.SMTP_HOST,
  port: 587,
  auth: {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASS,
  },
});

await transporter.sendMail({
  from: 'noreply@lgihe.org',
  to: 'safeguarding@lgihe.ac.ug',
  subject: `🚨 URGENT: Abuse Report [${reportId}]`,
  html: emailHtml,
});
```

### Alternative: SendGrid
```typescript
import sgMail from '@sendgrid/mail';

sgMail.setApiKey(process.env.SENDGRID_API_KEY!);

await sgMail.send({
  from: 'noreply@lgihe.org',
  to: 'safeguarding@lgihe.ac.ug',
  subject: `🚨 URGENT: Abuse Report [${reportId}]`,
  html: emailHtml,
});
```

---

## Monitoring & Logging

### What to Log
```typescript
console.log(`Abuse report submitted: ${reportId}`);
console.log(`- Type: ${formData.incidentType}`);
console.log(`- Anonymous: ${isAnonymous}`);
console.log(`- Timestamp: ${new Date().toISOString()}`);
```

### What NOT to Log
- Reporter's personal information
- Detailed incident descriptions
- Names of persons involved
- Any sensitive content from the report

---

## Common Issues & Solutions

### Issue: Email not received
1. Check spam/junk folder
2. Verify RESEND_API_KEY is set
3. Check Resend dashboard for delivery status
4. Verify sender domain is verified

### Issue: 400 Bad Request
1. Check all required fields are present
2. Verify field names match exactly
3. Ensure date format is YYYY-MM-DD
4. Check JSON is valid

### Issue: 500 Internal Server Error
1. Check server logs for details
2. Verify email service is configured
3. Test email service separately
4. Check environment variables

---

## Next Steps After Implementation

1. **Test thoroughly** with both anonymous and identified reports
2. **Set up monitoring** for email delivery
3. **Train staff** on handling reports
4. **Create response procedures** for different incident types
5. **Set up escalation paths** for urgent reports
6. **Regular audits** of the system
7. **Update documentation** as needed

---

## Support Contacts

- **Technical Issues**: tech@lgihe.ac.ug
- **Safeguarding Team**: safeguarding@lgihe.ac.ug
- **Emergency**: (+256) 414 222 517

---

**Document Version**: 1.0.0  
**Last Updated**: May 5, 2026  
**See Also**: `abuse-reporting-system.md` (full documentation)
