# Tender Listing Implementation

This document outlines the complete implementation of the tender listing functionality, similar to job listings but with support for two document types: RFP (Request for Proposal) and ToR (Terms of Reference).

## Features Implemented

### 1. Database Structure
- **Migration**: `2026_05_05_100000_add_document_fields_to_tenders_table.php`
  - Added RFP document fields: `rfp_document_path`, `rfp_document_name`, `rfp_document_type`, `rfp_document_size`
  - Added ToR document fields: `tor_document_path`, `tor_document_name`, `tor_document_type`, `tor_document_size`
  - Removed old `document_url` field

### 2. Model Updates
- **File**: `app/Models/Tender.php`
  - Added fillable fields for both RFP and ToR documents
  - Added `HasFactory` trait for testing
  - Implemented automatic `published_at` setting when status is 'open'
  - **Automatic document deletion**: When a tender is deleted (soft or force delete), associated RFP and ToR documents are automatically removed from storage
  - Added helper methods:
    - `getRfpDocumentUrlAttribute()` - Get full URL for RFP document
    - `getTorDocumentUrlAttribute()` - Get full URL for ToR document
    - `hasRfpDocument()` - Check if RFP document exists
    - `hasTorDocument()` - Check if ToR document exists
    - `getFormattedRfpFileSizeAttribute()` - Format RFP file size
    - `getFormattedTorFileSizeAttribute()` - Format ToR file size
  - Updated `scopeOpen()` to filter by valid closing dates

### 3. Filament Admin Panel
- **Resource**: `app/Filament/Resources/TenderResource.php`
  - Complete CRUD interface for tenders
  - Separate sections for RFP and ToR document uploads
  - File upload with automatic naming: `{reference-number}_{rfp|tor}_{monthyear}.{ext}`
  - Support for PDF and Word documents (max 10MB)
  - Status badges with colors (draft, open, closed, awarded, cancelled)
  - Category badges (goods, services, works, consultancy)
  - Download actions for both RFP and ToR documents
  - Filters by status and category
  - Permission-based access control

- **Pages**:
  - `CreateTender.php` - Create new tenders with document metadata handling
  - `EditTender.php` - Edit existing tenders with document updates
  - `ListTenders.php` - List all tenders with filters

### 4. API Endpoints
- **Controller**: `app/Http/Controllers/Api/V1/TenderController.php`
  - `GET /api/v1/tenders` - List all open tenders (paginated)
  - `GET /api/v1/tenders/{id}` - Show single tender details
  - `GET /api/v1/tenders/{tender}/download-rfp` - Download RFP document
  - `GET /api/v1/tenders/{tender}/download-tor` - Download ToR document

- **Routes**: Added in `routes/api.php`
  - All endpoints are public (no authentication required)
  - Download routes use route model binding

### 5. Permissions
- **Updated**: `database/seeders/PermissionSeeder.php`
  - Added tender permissions: `view_tenders`, `create_tenders`, `update_tenders`, `delete_tenders`
  - Permissions assigned to appropriate roles:
    - Super Admin: All permissions
    - Admin: All tender permissions
    - Editor: All tender permissions
    - Viewer: View tender permission only

### 6. Testing
- **Test File**: `tests/Feature/TenderTest.php`
  - 15 comprehensive tests covering:
    - API listing and showing tenders
    - Creating tenders with documents
    - File size formatting
    - Open tender filtering by closing date
    - Published_at automatic setting
    - Document downloads
    - 404 handling for missing documents
    - Automatic document deletion on soft delete
    - Automatic document deletion on force delete
    - Graceful handling when documents don't exist

- **Factory**: `database/factories/TenderFactory.php`
  - Support for creating test tenders
  - States: `open()`, `closed()`, `awarded()`
  - Document helpers: `withRfpDocument()`, `withTorDocument()`, `withDocuments()`

## API Response Format

### List Tenders
```json
{
  "data": [
    {
      "id": 1,
      "title": "Supply of Laboratory Equipment",
      "reference_number": "TENDER/2026/001",
      "description": "...",
      "category": "goods",
      "closing_date": "2026-06-15",
      "status": "open",
      "has_rfp_document": true,
      "has_tor_document": true,
      "rfp_download_url": "http://example.com/api/v1/tenders/1/download-rfp",
      "tor_download_url": "http://example.com/api/v1/tenders/1/download-tor",
      "creator": {
        "id": 1,
        "name": "Admin User"
      }
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Show Single Tender
```json
{
  "id": 1,
  "title": "Supply of Laboratory Equipment",
  "reference_number": "TENDER/2026/001",
  "description": "...",
  "requirements": "...",
  "category": "goods",
  "closing_date": "2026-06-15",
  "status": "open",
  "published_at": "2026-05-05T10:00:00.000000Z",
  "has_rfp_document": true,
  "has_tor_document": true,
  "rfp_download_url": "http://example.com/api/v1/tenders/1/download-rfp",
  "tor_download_url": "http://example.com/api/v1/tenders/1/download-tor",
  "formatted_rfp_file_size": "2.5 MB",
  "formatted_tor_file_size": "1.8 MB",
  "creator": {
    "id": 1,
    "name": "Admin User"
  }
}
```

## Frontend Integration

### Displaying Tenders
```javascript
// Fetch tenders
const response = await fetch('/api/v1/tenders');
const data = await response.json();

// Display tenders with download links
data.data.forEach(tender => {
  console.log(tender.title);
  
  if (tender.has_rfp_document) {
    // Show RFP download button
    // Link to: tender.rfp_download_url
  }
  
  if (tender.has_tor_document) {
    // Show ToR download button
    // Link to: tender.tor_download_url
  }
});
```

### Document Downloads
The download URLs can be used directly in anchor tags:
```html
<a href="{{ tender.rfp_download_url }}" download>
  Download RFP ({{ tender.formatted_rfp_file_size }})
</a>

<a href="{{ tender.tor_download_url }}" download>
  Download ToR ({{ tender.formatted_tor_file_size }})
</a>
```

## File Storage
- Documents are stored in `storage/app/public/tender-documents/`
  - RFP documents: `tender-documents/rfp/`
  - ToR documents: `tender-documents/tor/`
- Files are publicly accessible via the storage link
- Automatic file naming based on reference number and date
- **Automatic cleanup**: Documents are automatically deleted from storage when a tender is deleted (both soft delete and force delete)

## Status Flow
1. **Draft** - Initial state, not visible to public
2. **Open** - Published and accepting submissions (visible in API)
3. **Closed** - Submission period ended (not visible in API)
4. **Awarded** - Contract awarded
5. **Cancelled** - Tender cancelled

## Validation Rules
- Only tenders with status 'open' are shown in public API
- Closing date must be in the future for tender to appear
- Published_at must be set and not in the future
- Both RFP and ToR documents are optional
- Supported file types: PDF, DOC, DOCX
- Maximum file size: 10MB per document

## Next Steps for Frontend
1. Create tender listing page
2. Create tender detail page
3. Add download buttons for RFP and ToR documents
4. Display closing date with countdown
5. Add category and status badges
6. Implement search and filter functionality

## Features Implemented

### 1. Database Structure
- **Migration**: `2026_05_05_100000_add_document_fields_to_tenders_table.php`
  - Added RFQ document fields: `rfq_document_path`, `rfq_document_name`, `rfq_document_type`, `rfq_document_size`
  - Added ToR document fields: `tor_document_path`, `tor_document_name`, `tor_document_type`, `tor_document_size`
  - Removed old `document_url` field

### 2. Model Updates
- **File**: `app/Models/Tender.php`
  - Added fillable fields for both RFQ and ToR documents
  - Added `HasFactory` trait for testing
  - Implemented automatic `published_at` setting when status is 'open'
  - Added helper methods:
    - `getRfqDocumentUrlAttribute()` - Get full URL for RFQ document
    - `getTorDocumentUrlAttribute()` - Get full URL for ToR document
    - `hasRfqDocument()` - Check if RFQ document exists
    - `hasTorDocument()` - Check if ToR document exists
    - `getFormattedRfqFileSizeAttribute()` - Format RFQ file size
    - `getFormattedTorFileSizeAttribute()` - Format ToR file size
  - Updated `scopeOpen()` to filter by valid closing dates

### 3. Filament Admin Panel
- **Resource**: `app/Filament/Resources/TenderResource.php`
  - Complete CRUD interface for tenders
  - Separate sections for RFQ and ToR document uploads
  - File upload with automatic naming: `{reference-number}_{rfq|tor}_{monthyear}.{ext}`
  - Support for PDF and Word documents (max 10MB)
  - Status badges with colors (draft, open, closed, awarded, cancelled)
  - Category badges (goods, services, works, consultancy)
  - Download actions for both RFQ and ToR documents
  - Filters by status and category
  - Permission-based access control

- **Pages**:
  - `CreateTender.php` - Create new tenders with document metadata handling
  - `EditTender.php` - Edit existing tenders with document updates
  - `ListTenders.php` - List all tenders with filters

### 4. API Endpoints
- **Controller**: `app/Http/Controllers/Api/V1/TenderController.php`
  - `GET /api/v1/tenders` - List all open tenders (paginated)
  - `GET /api/v1/tenders/{id}` - Show single tender details
  - `GET /api/v1/tenders/{tender}/download-rfq` - Download RFQ document
  - `GET /api/v1/tenders/{tender}/download-tor` - Download ToR document

- **Routes**: Added in `routes/api.php`
  - All endpoints are public (no authentication required)
  - Download routes use route model binding

### 5. Permissions
- **Updated**: `database/seeders/PermissionSeeder.php`
  - Added tender permissions: `view_tenders`, `create_tenders`, `update_tenders`, `delete_tenders`
  - Permissions assigned to appropriate roles:
    - Super Admin: All permissions
    - Admin: All tender permissions
    - Editor: All tender permissions
    - Viewer: View tender permission only

### 6. Testing
- **Test File**: `tests/Feature/TenderTest.php`
  - 12 comprehensive tests covering:
    - API listing and showing tenders
    - Creating tenders with documents
    - File size formatting
    - Open tender filtering by closing date
    - Published_at automatic setting
    - Document downloads
    - 404 handling for missing documents

- **Factory**: `database/factories/TenderFactory.php`
  - Support for creating test tenders
  - States: `open()`, `closed()`, `awarded()`
  - Document helpers: `withRfqDocument()`, `withTorDocument()`, `withDocuments()`

## API Response Format

### List Tenders
```json
{
  "data": [
    {
      "id": 1,
      "title": "Supply of Laboratory Equipment",
      "reference_number": "TENDER/2026/001",
      "description": "...",
      "category": "goods",
      "closing_date": "2026-06-15",
      "status": "open",
      "has_rfq_document": true,
      "has_tor_document": true,
      "rfq_download_url": "http://example.com/api/v1/tenders/1/download-rfq",
      "tor_download_url": "http://example.com/api/v1/tenders/1/download-tor",
      "creator": {
        "id": 1,
        "name": "Admin User"
      }
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Show Single Tender
```json
{
  "id": 1,
  "title": "Supply of Laboratory Equipment",
  "reference_number": "TENDER/2026/001",
  "description": "...",
  "requirements": "...",
  "category": "goods",
  "closing_date": "2026-06-15",
  "status": "open",
  "published_at": "2026-05-05T10:00:00.000000Z",
  "has_rfq_document": true,
  "has_tor_document": true,
  "rfq_download_url": "http://example.com/api/v1/tenders/1/download-rfq",
  "tor_download_url": "http://example.com/api/v1/tenders/1/download-tor",
  "formatted_rfq_file_size": "2.5 MB",
  "formatted_tor_file_size": "1.8 MB",
  "creator": {
    "id": 1,
    "name": "Admin User"
  }
}
```

## Frontend Integration

### Displaying Tenders
```javascript
// Fetch tenders
const response = await fetch('/api/v1/tenders');
const data = await response.json();

// Display tenders with download links
data.data.forEach(tender => {
  console.log(tender.title);
  
  if (tender.has_rfq_document) {
    // Show RFQ download button
    // Link to: tender.rfq_download_url
  }
  
  if (tender.has_tor_document) {
    // Show ToR download button
    // Link to: tender.tor_download_url
  }
});
```

### Document Downloads
The download URLs can be used directly in anchor tags:
```html
<a href="{{ tender.rfq_download_url }}" download>
  Download RFQ ({{ tender.formatted_rfq_file_size }})
</a>

<a href="{{ tender.tor_download_url }}" download>
  Download ToR ({{ tender.formatted_tor_file_size }})
</a>
```

## File Storage
- Documents are stored in `storage/app/public/tender-documents/`
  - RFQ documents: `tender-documents/rfq/`
  - ToR documents: `tender-documents/tor/`
- Files are publicly accessible via the storage link
- Automatic file naming based on reference number and date

## Status Flow
1. **Draft** - Initial state, not visible to public
2. **Open** - Published and accepting submissions (visible in API)
3. **Closed** - Submission period ended (not visible in API)
4. **Awarded** - Contract awarded
5. **Cancelled** - Tender cancelled

## Validation Rules
- Only tenders with status 'open' are shown in public API
- Closing date must be in the future for tender to appear
- Published_at must be set and not in the future
- Both RFQ and ToR documents are optional
- Supported file types: PDF, DOC, DOCX
- Maximum file size: 10MB per document

## Next Steps for Frontend
1. Create tender listing page
2. Create tender detail page
3. Add download buttons for RFQ and ToR documents
4. Display closing date with countdown
5. Add category and status badges
6. Implement search and filter functionality
