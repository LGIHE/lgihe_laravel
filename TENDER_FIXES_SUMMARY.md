# Tender Document Metadata & Naming Fixes

## Issues Fixed

### 1. Null Document Metadata Fields
**Problem**: Document metadata fields (name, type, size) were showing as `null` in API responses.

**Solution**: 
- Updated `CreateTender.php` and `EditTender.php` to properly capture file metadata after upload
- Created `FixTenderDocumentMetadata` command to fix existing records
- Metadata is now automatically populated from the uploaded file using Storage facade

**Fixed Fields**:
- `rfp_document_name` - Now shows the actual filename
- `rfp_document_type` - Now shows MIME type (e.g., `application/pdf`)
- `rfp_document_size` - Now shows file size in bytes
- `tor_document_name` - Now shows the actual filename
- `tor_document_type` - Now shows MIME type
- `tor_document_size` - Now shows file size in bytes
- `formatted_rfp_file_size` - Now shows human-readable size (e.g., "124 KB")
- `formatted_tor_file_size` - Now shows human-readable size (e.g., "188.93 KB")

### 2. File Naming Convention
**Problem**: Files were named using reference number and date: `{reference-number}_{rfp|tor}_{monthyear}.{ext}`

**Solution**: 
- Updated `TenderResource.php` to use slug-based naming
- Created `RenameTenderDocuments` command to rename existing files
- New naming convention: `{slug}_RFP.{ext}` or `{slug}_ToR.{ext}`

**Examples**:
- Old: `lgiheklasrv0012026_rfp_052026.docx`
- New: `graphics-designer_RFP.docx`

- Old: `lgiheklasrv0012026_tor_052026.docx`
- New: `graphics-designer_ToR.docx`

## Files Modified

### 1. Filament Resource
**File**: `app/Filament/Resources/TenderResource.php`

**Changes**:
```php
// Old naming
return "{$sanitizedRef}_rfp_{$monthYear}.{$extension}";

// New naming
return "{$slug}_RFP.{$extension}";
```

### 2. Create Page
**File**: `app/Filament/Resources/TenderResource/Pages/CreateTender.php`

**Changes**:
- Updated `mutateFormDataBeforeCreate()` to capture file metadata from Storage
- Uses `Storage::disk('public')->size()` and `Storage::disk('public')->mimeType()`
- Handles both array and string file paths

### 3. Edit Page
**File**: `app/Filament/Resources/TenderResource/Pages/EditTender.php`

**Changes**:
- Updated `mutateFormDataBeforeSave()` to capture file metadata from Storage
- Same approach as CreateTender page

## New Commands Created

### 1. Fix Document Metadata
**Command**: `php artisan tender:fix-metadata`

**Purpose**: Fixes null metadata for existing tender documents

**What it does**:
- Scans all tenders (including soft-deleted)
- For each document that exists in storage:
  - Sets `document_name` from filename
  - Sets `document_size` from file size
  - Sets `document_type` from MIME type
- Saves changes without triggering model events

**Usage**:
```bash
php artisan tender:fix-metadata
```

### 2. Rename Documents
**Command**: `php artisan tender:rename-documents`

**Purpose**: Renames existing documents to follow new naming convention

**What it does**:
- Scans all tenders (including soft-deleted)
- For each document:
  - Creates new filename: `{slug}_RFP.{ext}` or `{slug}_ToR.{ext}`
  - Copies file to new location
  - Deletes old file
  - Updates database with new path and name
- Saves changes without triggering model events

**Usage**:
```bash
php artisan tender:rename-documents
```

## API Response - Before vs After

### Before (with null values)
```json
{
  "rfp_document_path": "tender-documents/rfp/lgiheklasrv0012026_rfp_052026.docx",
  "rfp_document_name": null,
  "rfp_document_type": null,
  "rfp_document_size": null,
  "tor_document_path": "tender-documents/tor/lgiheklasrv0012026_tor_052026.docx",
  "tor_document_name": null,
  "tor_document_type": null,
  "tor_document_size": null,
  "has_rfp_document": true,
  "has_tor_document": true,
  "rfp_download_url": "http://localhost:8000/api/v1/tenders/1/download-rfp",
  "tor_download_url": "http://localhost:8000/api/v1/tenders/1/download-tor",
  "formatted_rfp_file_size": null,
  "formatted_tor_file_size": null
}
```

### After (all fields populated)
```json
{
  "rfp_document_path": "tender-documents/rfp/graphics-designer_RFP.docx",
  "rfp_document_name": "graphics-designer_RFP.docx",
  "rfp_document_type": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  "rfp_document_size": 126980,
  "tor_document_path": "tender-documents/tor/graphics-designer_ToR.docx",
  "tor_document_name": "graphics-designer_ToR.docx",
  "tor_document_type": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  "tor_document_size": 193460,
  "has_rfp_document": true,
  "has_tor_document": true,
  "rfp_download_url": "http://localhost:8000/api/v1/tenders/1/download-rfp",
  "tor_download_url": "http://localhost:8000/api/v1/tenders/1/download-tor",
  "formatted_rfp_file_size": "124 KB",
  "formatted_tor_file_size": "188.93 KB"
}
```

## Testing
All 15 tests still passing ✅:
```
Tests:    15 passed (32 assertions)
Duration: 0.81s
```

## Migration Steps for Existing Data

If you have existing tenders with documents, run these commands in order:

```bash
# Step 1: Fix null metadata
php artisan tender:fix-metadata

# Step 2: Rename files to new convention
php artisan tender:rename-documents
```

## Future Uploads
All new tender documents uploaded through Filament will automatically:
- Use the new naming convention (`{slug}_RFP.{ext}` or `{slug}_ToR.{ext}`)
- Have all metadata fields populated (name, type, size)
- Show formatted file sizes in API responses

## Benefits

1. **Complete API Responses**: No more null values in document metadata
2. **Better File Organization**: Files named by slug are easier to identify
3. **Cleaner Filenames**: Shorter, more readable filenames
4. **Consistent Naming**: All files follow the same pattern
5. **Frontend Ready**: All data needed for display is available
