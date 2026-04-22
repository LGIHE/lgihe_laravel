# Job Listing Enhancements

This document describes the enhanced job listing functionality including organizational structure fields and document upload capabilities.

## New Features

### 1. Organizational Structure Fields

Job listings now include additional fields to define the organizational context:

- **Department**: The department or faculty where the position is located
- **Reports To**: The position or person this role reports to
- **Supervises Who**: Description of positions or people this role supervises

### 2. Document Upload

Job listings can now have attached documents (PDF or Word) containing detailed job descriptions that users can download.

## Database Changes

### New Fields Added to `job_listings` Table

```sql
ALTER TABLE job_listings ADD COLUMN department VARCHAR(255) NULL;
ALTER TABLE job_listings ADD COLUMN reports_to VARCHAR(255) NULL;
ALTER TABLE job_listings ADD COLUMN supervises_who TEXT NULL;
ALTER TABLE job_listings ADD COLUMN document_path VARCHAR(255) NULL;
ALTER TABLE job_listings ADD COLUMN document_name VARCHAR(255) NULL;
ALTER TABLE job_listings ADD COLUMN document_type VARCHAR(255) NULL;
ALTER TABLE job_listings ADD COLUMN document_size BIGINT NULL;
```

## API Changes

### Enhanced Job Listing Response

The API now returns additional fields:

```json
{
    "id": 1,
    "title": "Senior Lecturer - Computer Science",
    "department": "Computer Science Department",
    "reports_to": "Head of Department",
    "supervises_who": "Teaching Assistants, Research Associates",
    "has_document": true,
    "document_download_url": "https://example.com/api/v1/jobs/1/download-document",
    "formatted_file_size": "2.5 MB",
    "document_name": "Job Description.pdf",
    // ... other existing fields
}
```

### New API Endpoint

**Download Job Document**
- **Endpoint**: `GET /api/v1/jobs/{id}/download-document`
- **Description**: Downloads the attached job description document
- **Response**: File download with appropriate headers

## Admin Panel Changes

### Enhanced Job Creation Form

The Filament admin panel now includes:

1. **Job Information Section** - Enhanced with new fields:
   - Department (text input)
   - Reports To (text input)
   - Supervises Who (textarea)

2. **Job Description Document Section** - New collapsible section:
   - File upload component
   - Accepts PDF and Word documents
   - Maximum file size: 10MB
   - Automatic file information extraction

### Enhanced Job Listing Table

The admin table now shows:
- Department column (searchable, toggleable)
- Document indicator (icon column showing if document exists)
- Improved employment type badges with colors
- Better creator information display

### Document Management

- **Upload**: Drag and drop or click to upload PDF/Word documents
- **Preview**: Document name and size displayed after upload
- **Download**: Direct download action in table rows
- **Validation**: File type and size validation

## User Profile Management

### New Filament Page: My Profile

A dedicated profile page for logged-in users with:

1. **Profile Information Section**:
   - Update name and email
   - Real-time validation
   - Unique email constraint

2. **Password Update Section**:
   - Current password verification
   - New password with confirmation
   - Password strength requirements

3. **Account Information Display**:
   - Account creation date
   - Last update timestamp
   - User roles (with badges)
   - User permissions (with badges)

### Navigation

- Added to main navigation as "My Profile"
- Icon: User circle
- Sort order: 100 (appears at bottom)
- Access: All authenticated users

## File Storage

### Storage Configuration

Documents are stored using Laravel's file storage system:

- **Disk**: `public`
- **Directory**: `job-documents/`
- **Naming**: Automatic unique naming to prevent conflicts
- **Access**: Public access for downloads

### File Validation

- **Accepted Types**: PDF, DOC, DOCX
- **Maximum Size**: 10MB
- **Security**: File type validation and secure storage

## Security Considerations

### File Upload Security

1. **File Type Validation**: Only PDF and Word documents accepted
2. **Size Limits**: 10MB maximum to prevent abuse
3. **Secure Storage**: Files stored outside web root with controlled access
4. **Download Protection**: Files served through controller with proper headers

### Profile Security

1. **Authentication Required**: All profile operations require authentication
2. **Password Verification**: Current password required for password changes
3. **Input Validation**: All inputs validated and sanitized
4. **Email Uniqueness**: Prevents duplicate email addresses

## Usage Examples

### Creating a Job with Document

```php
// In Filament admin panel
$job = JobListing::create([
    'title' => 'Senior Lecturer - Computer Science',
    'department' => 'Computer Science Department',
    'reports_to' => 'Head of Department',
    'supervises_who' => 'Teaching Assistants, Research Associates',
    'document_path' => 'job-documents/senior-lecturer-cs.pdf',
    'document_name' => 'Senior Lecturer Job Description.pdf',
    'document_type' => 'application/pdf',
    'document_size' => 2048576,
    // ... other fields
]);
```

### Frontend Integration

```javascript
// Fetch job with document info
const job = await fetch('/api/v1/jobs/1').then(r => r.json());

if (job.has_document) {
    // Show download button
    const downloadBtn = document.createElement('a');
    downloadBtn.href = job.document_download_url;
    downloadBtn.textContent = `Download ${job.document_name} (${job.formatted_file_size})`;
    downloadBtn.target = '_blank';
}
```

### Profile Management

```javascript
// Update profile via API
const updateProfile = await fetch('/api/v1/profile', {
    method: 'PUT',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        name: 'Updated Name',
        email: 'new@example.com'
    })
});

// Change password
const changePassword = await fetch('/api/v1/profile/change-password', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        current_password: 'oldpass',
        password: 'newpass',
        password_confirmation: 'newpass'
    })
});
```

## Testing

Comprehensive test coverage includes:

- Job creation with new fields
- Document upload and attachment
- Document download functionality
- API response format validation
- File storage verification
- Profile management operations
- Security validations

Run tests with:
```bash
php artisan test tests/Feature/JobListingEnhancedTest.php
php artisan test tests/Feature/ProfileTest.php
```

## Migration

To apply these changes to an existing installation:

1. Run the migration:
   ```bash
   php artisan migrate
   ```

2. Ensure storage link exists:
   ```bash
   php artisan storage:link
   ```

3. Set appropriate file permissions for storage directory

4. Update any existing job listings to include the new fields as needed