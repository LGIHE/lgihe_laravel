# Job Listing Fields Documentation

## Complete Field Structure

### **Required Fields**

1. **Title** (text)
   - The job position title
   - Example: "Senior Lecturer - Computer Science"
   - Auto-generates slug from title

2. **Slug** (text, unique)
   - URL-friendly version of title
   - Auto-generated but can be customized
   - Example: "senior-lecturer-computer-science"

3. **Description** (rich text)
   - Detailed description of the position
   - Supports formatting: bold, italic, underline, lists, links
   - Example: Background information, context, overview

4. **Status** (select)
   - Options: Draft, Active, Closed, Archived
   - Controls visibility and availability

### **Optional Content Fields**

5. **Purpose of the Role** (rich text)
   - Positioned after Description
   - Explains the role's objectives and strategic importance
   - Example: "The purpose of this role is to..."

6. **Requirements** (rich text)
   - Required qualifications and skills
   - Education, experience, certifications
   - Example: "PhD in Computer Science, 5+ years experience..."

7. **Application Requirements** (rich text)
   - What applicants need to submit
   - Documents, portfolios, references
   - Example: "Please submit CV, cover letter, and..."

8. **Application Process** (rich text)
   - How to apply for the position
   - Steps, timeline, contact information
   - Example: "Submit applications via email to..."

9. **Responsibilities** (rich text)
   - Key duties and responsibilities
   - Day-to-day tasks and expectations
   - Example: "Teaching undergraduate courses, conducting research..."

10. **Core Competencies** (rich text)
    - Essential skills and abilities required
    - Technical and soft skills
    - Example: "Leadership, communication, technical expertise..."

11. **Disclaimer** (rich text)
    - Legal information or additional notes
    - Equal opportunity statements, terms
    - Example: "We are an equal opportunity employer..."

### **Organizational Fields**

12. **Location** (text)
    - Physical location of the position
    - Example: "Kampala, Uganda"

13. **Department** (text)
    - Department or faculty
    - Example: "Computer Science Department"

14. **Reports To** (text)
    - Position or person this role reports to
    - Example: "Head of Department"

15. **Supervises Who** (textarea)
    - Positions or people this role supervises
    - Example: "Teaching Assistants, Research Associates"

### **Employment Details**

16. **Employment Type** (select)
    - Options: Full-time, Part-time, Contract, Temporary
    - Defines the nature of employment

17. **Salary Range** (text)
    - Optional salary information
    - Example: "UGX 3,000,000 - 5,000,000"

18. **Application Deadline** (date)
    - Last date to accept applications
    - Used to determine if job is still accepting applications

### **Document Attachment**

19. **Job Description Document** (file upload)
    - PDF or Word document (max 10MB)
    - Detailed job description for download
    - Automatically captures:
      - document_path (file location)
      - document_name (display name)
      - document_size (file size in bytes)
      - document_type (MIME type)

### **Publishing Fields**

20. **Published At** (datetime)
    - When the job listing goes live
    - Auto-set when status is "Active"

21. **Created By** / **Updated By** (user references)
    - Tracks who created/modified the listing
    - Auto-populated

## Field Order in Form

The fields appear in the following sections:

### **Section 1: Job Details**
1. Title (required)
2. Slug (required)
3. Description (required)
4. Purpose of the Role
5. Requirements
6. Application Requirements
7. Application Process
8. Responsibilities
9. Core Competencies
10. Disclaimer

### **Section 2: Job Information**
1. Location
2. Department
3. Reports To
4. Supervises Who
5. Employment Type
6. Salary Range
7. Application Deadline

### **Section 3: Job Description Document**
1. Document Upload (PDF/Word)

### **Section 4: Publishing**
1. Status (required)
2. Published At (conditional)

## API Response Structure

```json
{
  "id": 1,
  "title": "Senior Lecturer - Computer Science",
  "slug": "senior-lecturer-computer-science",
  "description": "<p>Detailed description...</p>",
  "purpose_of_role": "<p>The purpose of this role...</p>",
  "requirements": "<p>PhD in Computer Science...</p>",
  "application_requirements": "<p>Please submit...</p>",
  "application_process": "<p>Applications should be...</p>",
  "responsibilities": "<p>Key responsibilities include...</p>",
  "core_competencies": "<p>Essential competencies...</p>",
  "disclaimer": "<p>Equal opportunity employer...</p>",
  "location": "Kampala, Uganda",
  "department": "Computer Science Department",
  "reports_to": "Head of Department",
  "supervises_who": "Teaching Assistants, Research Associates",
  "employment_type": "full-time",
  "salary_range": "UGX 3,000,000 - 5,000,000",
  "application_deadline": "2026-05-30T00:00:00.000000Z",
  "status": "active",
  "published_at": "2026-04-22T10:00:00.000000Z",
  "document_path": "job-documents/file.pdf",
  "document_name": "Job Description.pdf",
  "document_type": "application/pdf",
  "document_size": 2048576,
  "has_document": true,
  "document_download_url": "https://example.com/api/v1/jobs/1/download-document",
  "formatted_file_size": "2 MB",
  "created_at": "2026-04-22T10:00:00.000000Z",
  "updated_at": "2026-04-22T10:00:00.000000Z",
  "creator": {
    "id": 1,
    "name": "Admin User"
  }
}
```

## Field Validation Rules

### **Required Fields:**
- title: required, string, max 255
- slug: required, unique, string
- description: required, rich text
- status: required, enum (draft, active, closed, archived)

### **Optional Fields:**
- All other fields are optional
- Rich text fields support HTML formatting
- File upload: PDF/DOC/DOCX, max 10MB

## Usage Guidelines

### **When to Use Each Field:**

- **Purpose of Role**: Use when you need to explain the strategic importance or context of the position
- **Core Competencies**: List specific skills and abilities beyond basic requirements
- **Application Requirements**: Specify exactly what documents/materials applicants must provide
- **Application Process**: Explain the step-by-step process for applying
- **Disclaimer**: Add legal requirements, equal opportunity statements, or important notes

### **Best Practices:**

1. **Be Comprehensive**: Use all relevant fields to provide complete information
2. **Be Clear**: Write in clear, professional language
3. **Be Specific**: Provide concrete examples and requirements
4. **Be Consistent**: Use similar formatting across all job listings
5. **Keep Updated**: Review and update listings regularly

## Database Schema

```sql
CREATE TABLE job_listings (
    id BIGINT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description LONGTEXT NOT NULL,
    purpose_of_role LONGTEXT NULL,
    requirements LONGTEXT NULL,
    application_requirements LONGTEXT NULL,
    application_process LONGTEXT NULL,
    responsibilities LONGTEXT NULL,
    core_competencies LONGTEXT NULL,
    disclaimer LONGTEXT NULL,
    location VARCHAR(255) NULL,
    department VARCHAR(255) NULL,
    reports_to VARCHAR(255) NULL,
    supervises_who TEXT NULL,
    employment_type VARCHAR(255) NULL,
    salary_range VARCHAR(255) NULL,
    application_deadline DATE NULL,
    document_path VARCHAR(255) NULL,
    document_name VARCHAR(255) NULL,
    document_type VARCHAR(255) NULL,
    document_size BIGINT NULL,
    status ENUM('draft', 'active', 'closed', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_by BIGINT NOT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```