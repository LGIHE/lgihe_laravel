# Abuse Reporting System Documentation

## Overview

The Abuse Reporting System is a confidential platform that allows students, staff, and community members to report incidents of abuse, harassment, discrimination, and other safety concerns at LGIHE. The system supports both identified and anonymous reporting.

---

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Frontend Implementation](#frontend-implementation)
3. [Backend API](#backend-api)
4. [Form Fields Reference](#form-fields-reference)
5. [Email Template](#email-template)
6. [Security & Privacy](#security--privacy)
7. [Backend Implementation Guide](#backend-implementation-guide)
8. [Testing](#testing)
9. [Maintenance](#maintenance)

---

## System Architecture

### Components

- **Frontend Page**: `/app/report-abuse/page.tsx`
- **API Route**: `/app/api/report-abuse/route.ts`
- **Footer Link**: Added to `components/Footer.tsx`
- **Email Service**: Uses Resend API (via `/lib/resend`)

### Data Flow

```
User fills form → Frontend validation → POST to /api/report-abuse → 
Backend validation → Generate Report ID → Send email to safeguarding team → 
Return success/error response → Display confirmation to user
```

---

## Frontend Implementation

### Page Location
`/app/report-abuse/page.tsx`

### Key Features

1. **Anonymous Reporting Option**
   - Checkbox to submit anonymously
   - Conditionally hides/shows contact fields
   - Allows victims to report without fear of identification

2. **Form Validation**
   - Client-side validation for required fields
   - HTML5 validation (email, date, required)
   - Custom validation messages

3. **User Feedback**
   - Success message with confirmation
   - Error handling with user-friendly messages
   - Loading states during submission

4. **Accessibility**
   - Proper ARIA labels
   - Keyboard navigation support
   - Screen reader friendly
   - High contrast emergency notices

5. **Emergency Information**
   - Prominent emergency contact display
   - Police and campus security numbers
   - External support resources

### UI Components

- **Alert Banners**: Success/error messages
- **Information Boxes**: Important notices, emergency contacts
- **Form Sections**: Reporter info, incident details
- **Support Resources**: On-campus and external help

---

## Backend API

### Endpoint
**POST** `/api/report-abuse`

### Request Headers
```
Content-Type: application/json
```

### Request Body
See [Form Fields Reference](#form-fields-reference) section below.

### Response Format

#### Success Response (200)
```json
{
  "success": true,
  "message": "Report submitted successfully",
  "reportId": "ABR-1234567890-ABC123XYZ"
}
```

#### Error Response (400/500)
```json
{
  "success": false,
  "message": "Error description"
}
```

### Error Codes

| Status Code | Description |
|-------------|-------------|
| 400 | Bad Request - Missing required fields |
| 500 | Internal Server Error - Email service failure or server error |

---

## Form Fields Reference

### Reporter Information (Optional for Anonymous Reports)

| Field Name | Type | Required | Description |
|------------|------|----------|-------------|
| `reporterName` | string | No* | Full name of the person submitting the report |
| `reporterEmail` | email | No* | Email address for follow-up communication |
| `reporterPhone` | string | No* | Phone number for contact |
| `reporterRelationship` | select | No* | Relationship to incident (victim, witness, third-party, concerned-party, other) |
| `preferredContact` | select | No* | Preferred contact method (email, phone, no-contact) |
| `anonymousReport` | boolean | No | Whether this is an anonymous report |

*Not required if `anonymousReport` is true

### Incident Details (Required)

| Field Name | Type | Required | Description |
|------------|------|----------|-------------|
| `incidentType` | select | **Yes** | Type of abuse/incident |
| `incidentDate` | date | **Yes** | Date when the incident occurred |
| `incidentLocation` | string | **Yes** | Location where incident took place |
| `personsInvolved` | textarea | **Yes** | Names/descriptions of persons involved |
| `detailedDescription` | textarea | **Yes** | Comprehensive description of the incident |
| `witnessesPresent` | textarea | No | Information about witnesses |
| `previouslyReported` | textarea | No | Details if incident was reported before |
| `evidenceAvailable` | textarea | No | Description of available evidence |

### Incident Type Options

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

## Email Template

### Email Recipients
- **Primary**: `safeguarding@lgihe.ac.ug`
- **Reply-To**: Reporter's email (if provided) or safeguarding email

### Email Subject Format
```
🚨 URGENT: Abuse Report [REPORT-ID] - [INCIDENT-TYPE]
```

Example:
```
🚨 URGENT: Abuse Report [ABR-1715234567-XYZ123ABC] - sexual-harassment
```

### Report ID Format
```
ABR-[TIMESTAMP]-[RANDOM-STRING]
```

Example: `ABR-1715234567890-ABC123XYZ`

- **ABR**: Abuse Report prefix
- **TIMESTAMP**: Unix timestamp in milliseconds
- **RANDOM-STRING**: 9-character alphanumeric string

### Email Content Sections

1. **Header**: Red banner with "CONFIDENTIAL ABUSE REPORT"
2. **Report ID Banner**: Yellow banner with report ID, submission date, and report type
3. **Reporter Information**: Contact details (if not anonymous)
4. **Incident Details**: Type, date, location
5. **Persons Involved**: Names/descriptions
6. **Detailed Description**: Full incident narrative
7. **Witnesses**: Witness information (if provided)
8. **Previous Reports**: Prior reporting history (if provided)
9. **Evidence**: Available evidence description (if provided)
10. **Action Required**: Checklist for safeguarding team
11. **Confidentiality Notice**: Legal and policy reminders
12. **Footer**: Contact information

---

## Security & Privacy

### Data Protection Measures

1. **HTTPS Only**: All form submissions over encrypted connection
2. **No Data Storage**: Reports are immediately emailed, not stored in database
3. **Confidential Email**: Sent to dedicated safeguarding email
4. **Anonymous Option**: Full anonymity support
5. **No File Uploads**: Prevents malicious file uploads; evidence described only

### Privacy Considerations

- Reports are marked as CONFIDENTIAL
- Anonymous reports contain no identifying information
- Email service logs are minimal
- No client-side tracking on the report page
- Report ID allows tracking without exposing reporter identity

### Compliance

- GDPR considerations for data handling
- Institutional safeguarding policies
- Mandatory reporting requirements (varies by jurisdiction)

---

## Backend Implementation Guide

### Prerequisites

1. **Email Service Configuration**
   - Resend API key configured in environment variables
   - Verified sender domain (`noreply@lgihe.org`)
   - Safeguarding email address (`safeguarding@lgihe.ac.ug`) set up

2. **Environment Variables**
   ```env
   RESEND_API_KEY=your_resend_api_key_here
   ```

3. **Resend Library**
   - Ensure `/lib/resend.ts` is properly configured
   - Example configuration:
   ```typescript
   import { Resend } from 'resend';
   
   export const resend = new Resend(process.env.RESEND_API_KEY);
   ```

### Implementation Steps

#### Step 1: Verify Email Service
```bash
# Test that Resend is working
curl -X POST https://api.resend.com/emails \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "from": "noreply@lgihe.org",
    "to": "test@example.com",
    "subject": "Test",
    "html": "<p>Test email</p>"
  }'
```

#### Step 2: Set Up Safeguarding Email
- Create `safeguarding@lgihe.ac.ug` email account
- Configure email forwarding to appropriate staff
- Set up auto-responder (optional)
- Test email delivery

#### Step 3: Configure Email Alerts (Optional)
Consider setting up:
- SMS alerts for urgent reports
- Slack/Teams notifications
- Multiple recipient emails
- Escalation procedures

#### Step 4: Database Integration (Optional Enhancement)

If you want to store reports in a database:

```typescript
// Example Prisma schema
model AbuseReport {
  id                    String   @id @default(cuid())
  reportId              String   @unique
  reporterName          String?
  reporterEmail         String?
  reporterPhone         String?
  reporterRelationship  String?
  incidentType          String
  incidentDate          DateTime
  incidentLocation      String
  personsInvolved       String
  detailedDescription   String
  witnessesPresent      String?
  previouslyReported    String?
  evidenceAvailable     String?
  preferredContact      String?
  anonymousReport       Boolean  @default(false)
  status                String   @default("pending")
  createdAt             DateTime @default(now())
  updatedAt             DateTime @updatedAt
}
```

Then modify the API route:

```typescript
// In /app/api/report-abuse/route.ts
import { prisma } from '@/lib/prisma';

// After generating reportId, before sending email:
await prisma.abuseReport.create({
  data: {
    reportId,
    ...formData,
    incidentDate: new Date(formData.incidentDate),
  },
});
```

#### Step 5: Set Up Monitoring

Monitor the following:
- Email delivery success rate
- API response times
- Error rates
- Report submission volume

Example logging:
```typescript
// Add to route.ts
import { logger } from '@/lib/logger';

logger.info('Abuse report submitted', {
  reportId,
  incidentType: formData.incidentType,
  isAnonymous,
  timestamp: new Date().toISOString(),
});
```

### Alternative Email Services

If not using Resend, you can adapt the code for:

#### Nodemailer (SMTP)
```typescript
import nodemailer from 'nodemailer';

const transporter = nodemailer.createTransport({
  host: process.env.SMTP_HOST,
  port: parseInt(process.env.SMTP_PORT || '587'),
  secure: false,
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

#### SendGrid
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

#### AWS SES
```typescript
import { SESClient, SendEmailCommand } from '@aws-sdk/client-ses';

const ses = new SESClient({ region: 'us-east-1' });

await ses.send(new SendEmailCommand({
  Source: 'noreply@lgihe.org',
  Destination: { ToAddresses: ['safeguarding@lgihe.ac.ug'] },
  Message: {
    Subject: { Data: `🚨 URGENT: Abuse Report [${reportId}]` },
    Body: { Html: { Data: emailHtml } },
  },
}));
```

---

## Testing

### Manual Testing Checklist

#### Frontend Testing
- [ ] Form loads correctly
- [ ] All fields are visible and functional
- [ ] Anonymous checkbox hides/shows contact fields
- [ ] Required field validation works
- [ ] Date picker functions properly
- [ ] Dropdown menus populate correctly
- [ ] Submit button shows loading state
- [ ] Success message displays after submission
- [ ] Error message displays on failure
- [ ] Form resets after successful submission
- [ ] Emergency contact information is visible
- [ ] Support resources section displays
- [ ] Page is responsive on mobile devices
- [ ] Accessibility features work (keyboard navigation, screen readers)

#### Backend Testing
- [ ] API accepts valid POST requests
- [ ] API rejects requests with missing required fields
- [ ] Report ID is generated correctly
- [ ] Email is sent to safeguarding team
- [ ] Email contains all submitted information
- [ ] Anonymous reports are handled correctly
- [ ] Non-anonymous reports include contact info
- [ ] Error handling works for email failures
- [ ] Response format is correct
- [ ] CORS headers are set (if needed)

### Test Cases

#### Test Case 1: Anonymous Report
```json
POST /api/report-abuse
{
  "anonymousReport": true,
  "incidentType": "bullying",
  "incidentDate": "2026-05-01",
  "incidentLocation": "Library",
  "personsInvolved": "Student A",
  "detailedDescription": "Test description"
}
```
**Expected**: Success response, email sent without reporter info

#### Test Case 2: Identified Report
```json
POST /api/report-abuse
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
  "detailedDescription": "Detailed test description",
  "witnessesPresent": "Student B, Student C",
  "previouslyReported": "No",
  "evidenceAvailable": "Audio recording"
}
```
**Expected**: Success response, email sent with full reporter info

#### Test Case 3: Missing Required Fields
```json
POST /api/report-abuse
{
  "incidentType": "bullying"
}
```
**Expected**: 400 error, "Required fields are missing"

### Automated Testing (Optional)

```typescript
// Example Jest test
import { POST } from '@/app/api/report-abuse/route';

describe('Abuse Report API', () => {
  it('should reject requests with missing fields', async () => {
    const request = new Request('http://localhost/api/report-abuse', {
      method: 'POST',
      body: JSON.stringify({ incidentType: 'bullying' }),
    });
    
    const response = await POST(request);
    const data = await response.json();
    
    expect(response.status).toBe(400);
    expect(data.success).toBe(false);
  });
  
  it('should accept valid anonymous reports', async () => {
    const request = new Request('http://localhost/api/report-abuse', {
      method: 'POST',
      body: JSON.stringify({
        anonymousReport: true,
        incidentType: 'bullying',
        incidentDate: '2026-05-01',
        incidentLocation: 'Library',
        personsInvolved: 'Student A',
        detailedDescription: 'Test description',
      }),
    });
    
    const response = await POST(request);
    const data = await response.json();
    
    expect(response.status).toBe(200);
    expect(data.success).toBe(true);
    expect(data.reportId).toMatch(/^ABR-\d+-[A-Z0-9]+$/);
  });
});
```

---

## Maintenance

### Regular Tasks

1. **Weekly**
   - Check email delivery logs
   - Verify safeguarding email is monitored
   - Review any error logs

2. **Monthly**
   - Test form submission end-to-end
   - Review and update incident type options if needed
   - Check for any security updates

3. **Quarterly**
   - Review and update support resources
   - Update emergency contact information
   - Audit email template for clarity

4. **Annually**
   - Review compliance with data protection laws
   - Update privacy statements
   - Train staff on report handling procedures

### Monitoring Metrics

Track these metrics:
- Number of reports submitted (anonymous vs identified)
- Report types distribution
- Response times to reports
- Email delivery success rate
- Page load times
- Form abandonment rate

### Troubleshooting

#### Issue: Emails not being received
**Solutions**:
1. Check RESEND_API_KEY is set correctly
2. Verify sender domain is verified in Resend
3. Check spam/junk folders
4. Review Resend dashboard for delivery status
5. Test with a different email address

#### Issue: Form submission fails
**Solutions**:
1. Check browser console for errors
2. Verify API route is accessible
3. Check network tab for request/response
4. Ensure all required fields are filled
5. Test with different browsers

#### Issue: Anonymous reports showing contact info
**Solutions**:
1. Verify `anonymousReport` field is being sent correctly
2. Check conditional rendering logic in email template
3. Test with explicit `anonymousReport: true` in request

---

## Contact & Support

For technical issues or questions about this system:
- **IT Support**: tech@lgihe.ac.ug
- **Safeguarding Team**: safeguarding@lgihe.ac.ug
- **Emergency**: (+256) 414 222 517

---

## Changelog

### Version 1.0.0 (May 5, 2026)
- Initial implementation
- Anonymous and identified reporting
- Email notification system
- Comprehensive form validation
- Emergency contact information
- Support resources section
- Footer link integration

---

## License & Confidentiality

This documentation is confidential and intended for LGIHE staff only. The abuse reporting system handles sensitive information and must be maintained according to institutional policies and applicable laws.

**Document Version**: 1.0.0  
**Last Updated**: May 5, 2026  
**Maintained By**: LGIHE IT Department
