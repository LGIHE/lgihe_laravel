# Tender Implementation - Changes Summary

## Overview
Complete tender listing functionality has been implemented with RFP (Request for Proposal) and ToR (Terms of Reference) document support, including automatic document cleanup on deletion.

## Files Created

### 1. Migration
- `database/migrations/2026_05_05_100000_add_document_fields_to_tenders_table.php`
  - Added RFP document fields (path, name, type, size)
  - Added ToR document fields (path, name, type, size)
  - Removed old `document_url` field

### 2. Filament Resources
- `app/Filament/Resources/TenderResource.php` - Main resource with CRUD interface
- `app/Filament/Resources/TenderResource/Pages/CreateTender.php` - Create page
- `app/Filament/Resources/TenderResource/Pages/EditTender.php` - Edit page
- `app/Filament/Resources/TenderResource/Pages/ListTenders.php` - List page

### 3. Factory & Seeder
- `database/factories/TenderFactory.php` - Factory for testing
- `database/seeders/TenderSeeder.php` - Sample data seeder

### 4. Tests
- `tests/Feature/TenderTest.php` - 15 comprehensive tests

### 5. Documentation
- `docs/TENDER_IMPLEMENTATION.md` - Complete implementation guide

## Files Modified

### 1. Model
**File**: `app/Models/Tender.php`

**Changes**:
- Added `HasFactory` trait
- Added RFP and ToR document fields to `$fillable`
- Added `boot()` method with:
  - `creating` event: Auto-set `published_at` when status is 'open'
  - `updating` event: Auto-set `published_at` when status changes to 'open'
  - `deleting` event: Delete RFP and ToR documents from storage
  - `forceDeleting` event: Delete RFP and ToR documents from storage
- Added helper methods:
  - `getRfpDocumentUrlAttribute()` - Get full URL for RFP document
  - `getTorDocumentUrlAttribute()` - Get full URL for ToR document
  - `hasRfpDocument()` - Check if RFP document exists
  - `hasTorDocument()` - Check if ToR document exists
  - `getFormattedRfpFileSizeAttribute()` - Format RFP file size
  - `getFormattedTorFileSizeAttribute()` - Format ToR file size
  - `formatFileSize()` - Private helper for file size formatting

### 2. Controller
**File**: `app/Http/Controllers/Api/V1/TenderController.php`

**Changes**:
- Updated `index()` method to include document info in response
- Updated `show()` method to include document info and formatted file sizes
- Added `downloadRfpDocument()` method for RFP downloads
- Added `downloadTorDocument()` method for ToR downloads

### 3. Routes
**File**: `routes/api.php`

**Changes**:
- Added `GET /api/v1/tenders/{tender}/download-rfp` route
- Added `GET /api/v1/tenders/{tender}/download-tor` route

### 4. Permissions
**File**: `database/seeders/PermissionSeeder.php`

**Changes**:
- Added `tenders` resource to permissions array
- Added tender permissions to Editor role

## Key Features

### 1. Document Management
- **Two document types**: RFP (Request for Proposal) and ToR (Terms of Reference)
- **Automatic naming**: Files are named as `{reference-number}_{rfp|tor}_{monthyear}.{ext}`
- **File validation**: PDF and Word documents only, max 10MB per file
- **Storage location**: `storage/app/public/tender-documents/rfp/` and `.../tor/`
- **Automatic cleanup**: Documents are deleted when tender is deleted (soft or force delete)

### 2. Status Management
- **Draft**: Not visible to public
- **Open**: Published and visible in API (only if closing date is in future)
- **Closed**: Submission period ended
- **Awarded**: Contract awarded
- **Cancelled**: Tender cancelled

### 3. API Endpoints
```
GET  /api/v1/tenders                        - List open tenders
GET  /api/v1/tenders/{id}                   - Show tender details
GET  /api/v1/tenders/{tender}/download-rfp  - Download RFP document
GET  /api/v1/tenders/{tender}/download-tor  - Download ToR document
```

### 4. Permissions
- `view_tenders` - View tenders in admin panel
- `create_tenders` - Create new tenders
- `update_tenders` - Edit existing tenders
- `delete_tenders` - Delete tenders

### 5. Filament Features
- Separate sections for RFP and ToR uploads
- Status and category badges with colors
- Download actions in table
- Filters by status and category
- Soft delete support with restore capability

## Testing
All 15 tests passing ✅:
- API listing and showing
- Document creation and management
- File size formatting
- Status filtering
- Document downloads
- Automatic document deletion
- Error handling

## Database Changes
Run migration:
```bash
php artisan migrate
```

Run permission seeder:
```bash
php artisan db:seed --class=PermissionSeeder
```

Optional - seed sample data:
```bash
php artisan db:seed --class=TenderSeeder
```

## API Response Example
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
  "has_rfp_document": true,
  "has_tor_document": true,
  "rfp_download_url": "http://example.com/api/v1/tenders/1/download-rfp",
  "tor_download_url": "http://example.com/api/v1/tenders/1/download-tor",
  "formatted_rfp_file_size": "2.5 MB",
  "formatted_tor_file_size": "1.8 MB"
}
```

## Frontend Integration Notes
1. Use `has_rfp_document` and `has_tor_document` to conditionally show download buttons
2. Use `rfp_download_url` and `tor_download_url` for direct downloads
3. Display `formatted_rfp_file_size` and `formatted_tor_file_size` next to download buttons
4. Filter tenders by category (goods, services, works, consultancy)
5. Show countdown to closing date
6. Display status badges with appropriate colors

## Correction Applied
- Changed all references from RFQ (Request for Quotation) to RFP (Request for Proposal)
- Updated database fields, methods, routes, and documentation accordingly
