# Analytics Integration Documentation

## Overview

The LGIHE website analytics system collects usage data and sends it to both:
1. **Local Next.js API** - For immediate access and the Next.js dashboard
2. **Laravel Backend** - For centralized analytics dashboard and long-term storage

All analytics respect user privacy preferences managed through the consent system.

---

## Backend API Endpoints

Your Laravel backend should implement the following endpoints to receive analytics data:

### Base URL
```
{NEXT_PUBLIC_API_URL}/analytics
```

Example: `http://localhost:8000/api/v1/analytics`

---

## 1. Event Tracking

### Endpoint
```
POST /analytics/event
```

### Description
Tracks user interactions and custom events (page views, button clicks, form submissions, etc.)

### Request Body
```json
{
  "name": "string",                    // Event name (e.g., "page_view", "button_click")
  "properties": {                      // Optional event-specific data
    "page": "string",
    "button": "string",
    "location": "string",
    "form": "string",
    "success": "boolean"
  },
  "timestamp": "2026-04-22T10:30:00.000Z",
  "sessionId": "string",               // Unique session identifier
  "userAgent": "string",               // Browser user agent
  "referrer": "string",                // Referring URL
  "screenResolution": "string",        // e.g., "1920x1080"
  "country": "string",                 // Only if user consented to geolocation
  "countryCode": "string",             // Only if user consented to geolocation
  "city": "string"                     // Only if user consented to geolocation
}
```

### Common Event Types
- `page_view` - User views a page
  - Properties: `{ page: "/academics" }`
- `button_click` - User clicks a button
  - Properties: `{ button: "Apply Now", location: "Hero Section" }`
- `form_submission` - User submits a form
  - Properties: `{ form: "Contact Form", success: true }`
- `download` - User downloads a file
  - Properties: `{ file: "prospectus.pdf" }`
- `video_play` - User plays a video
  - Properties: `{ video: "Campus Tour" }`

### Expected Response
```json
{
  "success": true
}
```

---

## 2. Page Load Performance

### Endpoint
```
POST /analytics/pageload
```

### Description
Tracks page load performance metrics for monitoring site speed

### Request Body
```json
{
  "url": "string",                     // Page URL path (e.g., "/academics")
  "loadTime": "number",                // Load time in milliseconds
  "timestamp": "2026-04-22T10:30:00.000Z",
  "userAgent": "string",               // Browser user agent
  "sessionId": "string",               // Unique session identifier
  "country": "string",                 // Only if user consented to geolocation
  "countryCode": "string",             // Only if user consented to geolocation
  "city": "string"                     // Only if user consented to geolocation
}
```

### Example
```json
{
  "url": "/academics/programmes",
  "loadTime": 1250,
  "timestamp": "2026-04-22T10:30:00.000Z",
  "userAgent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)...",
  "sessionId": "session_1713780600000_abc123xyz",
  "country": "Uganda",
  "countryCode": "UG",
  "city": "Kampala"
}
```

### Expected Response
```json
{
  "success": true
}
```

---

## 3. Error Logging

### Endpoint
```
POST /analytics/error
```

### Description
Logs JavaScript errors and unhandled promise rejections for debugging

### Request Body
```json
{
  "message": "string",                 // Error message
  "stack": "string",                   // Stack trace (optional)
  "url": "string",                     // URL where error occurred
  "userAgent": "string",               // Browser user agent
  "timestamp": "2026-04-22T10:30:00.000Z",
  "severity": "string"                 // "low" | "medium" | "high" | "critical"
}
```

### Example
```json
{
  "message": "Uncaught TypeError: Cannot read property 'map' of undefined",
  "stack": "TypeError: Cannot read property 'map' of undefined\n    at NewsSection (NewsSection.tsx:88:19)...",
  "url": "http://localhost:3000/",
  "userAgent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)...",
  "timestamp": "2026-04-22T10:30:00.000Z",
  "severity": "high"
}
```

### Severity Levels
- `low` - Minor issues, cosmetic problems
- `medium` - Non-critical functionality issues
- `high` - Important functionality broken
- `critical` - Site-breaking errors, security issues

### Expected Response
```json
{
  "success": true
}
```

---

## Data Privacy & Consent

### Consent Types
The analytics system respects three types of user consent:

1. **Analytics Consent** - Required for event tracking
   - If declined: No events are tracked
   
2. **Performance Consent** - Required for performance monitoring
   - If declined: Page load metrics are not collected
   
3. **Geolocation Consent** - Required for geographic data
   - If declined: `country`, `countryCode`, and `city` fields are omitted

### Data Exclusions
- Dashboard routes (`/dashboard/*`) are never tracked
- Users can opt out via the privacy preferences page (`/privacy/preferences`)

---

## Database Schema Recommendations

### Events Table
```sql
CREATE TABLE analytics_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    properties JSON,
    session_id VARCHAR(255),
    user_agent TEXT,
    referrer TEXT,
    screen_resolution VARCHAR(50),
    country VARCHAR(100),
    country_code VARCHAR(10),
    city VARCHAR(100),
    timestamp TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_session (session_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_country (country_code)
);
```

### Page Loads Table
```sql
CREATE TABLE analytics_pageloads (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    url VARCHAR(500) NOT NULL,
    load_time INT NOT NULL,
    session_id VARCHAR(255),
    user_agent TEXT,
    country VARCHAR(100),
    country_code VARCHAR(10),
    city VARCHAR(100),
    timestamp TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_url (url),
    INDEX idx_session (session_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_load_time (load_time)
);
```

### Errors Table
```sql
CREATE TABLE analytics_errors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message TEXT NOT NULL,
    stack TEXT,
    url TEXT,
    user_agent TEXT,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    timestamp TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_severity (severity),
    INDEX idx_timestamp (timestamp)
);
```

---

## Laravel Controller Example

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageLoad;
use App\Models\AnalyticsError;

class AnalyticsController extends Controller
{
    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'properties' => 'nullable|array',
            'timestamp' => 'required|date',
            'sessionId' => 'nullable|string|max:255',
            'userAgent' => 'nullable|string',
            'referrer' => 'nullable|string',
            'screenResolution' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'countryCode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
        ]);

        AnalyticsEvent::create([
            'name' => $validated['name'],
            'properties' => $validated['properties'] ?? null,
            'session_id' => $validated['sessionId'] ?? null,
            'user_agent' => $validated['userAgent'] ?? null,
            'referrer' => $validated['referrer'] ?? null,
            'screen_resolution' => $validated['screenResolution'] ?? null,
            'country' => $validated['country'] ?? null,
            'country_code' => $validated['countryCode'] ?? null,
            'city' => $validated['city'] ?? null,
            'timestamp' => $validated['timestamp'],
        ]);

        return response()->json(['success' => true]);
    }

    public function storePageLoad(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|string|max:500',
            'loadTime' => 'required|integer',
            'timestamp' => 'required|date',
            'sessionId' => 'nullable|string|max:255',
            'userAgent' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'countryCode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
        ]);

        AnalyticsPageLoad::create([
            'url' => $validated['url'],
            'load_time' => $validated['loadTime'],
            'session_id' => $validated['sessionId'] ?? null,
            'user_agent' => $validated['userAgent'] ?? null,
            'country' => $validated['country'] ?? null,
            'country_code' => $validated['countryCode'] ?? null,
            'city' => $validated['city'] ?? null,
            'timestamp' => $validated['timestamp'],
        ]);

        return response()->json(['success' => true]);
    }

    public function storeError(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'stack' => 'nullable|string',
            'url' => 'nullable|string',
            'userAgent' => 'nullable|string',
            'timestamp' => 'required|date',
            'severity' => 'required|in:low,medium,high,critical',
        ]);

        AnalyticsError::create([
            'message' => $validated['message'],
            'stack' => $validated['stack'] ?? null,
            'url' => $validated['url'] ?? null,
            'user_agent' => $validated['userAgent'] ?? null,
            'severity' => $validated['severity'],
            'timestamp' => $validated['timestamp'],
        ]);

        return response()->json(['success' => true]);
    }
}
```

---

## Laravel Routes Example

```php
// routes/api.php

use App\Http\Controllers\Api\V1\AnalyticsController;

Route::prefix('v1')->group(function () {
    Route::prefix('analytics')->group(function () {
        Route::post('/event', [AnalyticsController::class, 'storeEvent']);
        Route::post('/pageload', [AnalyticsController::class, 'storePageLoad']);
        Route::post('/error', [AnalyticsController::class, 'storeError']);
    });
});
```

---

## Testing the Integration

### 1. Set Environment Variable
```bash
# .env.local
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
```

### 2. Test Event Tracking
```bash
curl -X POST http://localhost:8000/api/v1/analytics/event \
  -H "Content-Type: application/json" \
  -d '{
    "name": "page_view",
    "properties": {"page": "/test"},
    "timestamp": "2026-04-22T10:30:00.000Z",
    "sessionId": "test_session_123"
  }'
```

### 3. Test Page Load Tracking
```bash
curl -X POST http://localhost:8000/api/v1/analytics/pageload \
  -H "Content-Type: application/json" \
  -d '{
    "url": "/test",
    "loadTime": 1500,
    "timestamp": "2026-04-22T10:30:00.000Z",
    "sessionId": "test_session_123"
  }'
```

### 4. Test Error Logging
```bash
curl -X POST http://localhost:8000/api/v1/analytics/error \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Test error",
    "severity": "low",
    "timestamp": "2026-04-22T10:30:00.000Z"
  }'
```

---

## Dashboard Metrics

Your Laravel dashboard can calculate these metrics from the collected data:

### Key Metrics
- **Total Page Views** - Count of `page_view` events
- **Unique Visitors** - Count of distinct `session_id`
- **Average Load Time** - Average of `load_time` from pageloads
- **Error Rate** - (Total errors / Total events) × 100
- **Bounce Rate** - Sessions with only 1 page view / Total sessions

### Breakdowns
- **Most Viewed Pages** - Group by `properties.page`
- **Traffic by Country** - Group by `country_code`
- **Device Types** - Parse `user_agent` for mobile/desktop/tablet
- **Browser Distribution** - Parse `user_agent` for browser type
- **Top Referrers** - Group by `referrer` domain
- **Hourly Traffic** - Group by hour from `timestamp`
- **Daily Traffic** - Group by date from `timestamp`

### Real-time Metrics
- **Active Visitors** - Count distinct sessions in last 5 minutes
- **Recent Errors** - Errors in last hour, grouped by severity

---

## Data Retention

Recommended retention policies:

- **Events**: 90 days (or longer for trend analysis)
- **Page Loads**: 90 days
- **Errors**: 30 days (archive critical errors separately)

Implement automatic cleanup:
```php
// app/Console/Commands/CleanupAnalytics.php
AnalyticsEvent::where('created_at', '<', now()->subDays(90))->delete();
AnalyticsPageLoad::where('created_at', '<', now()->subDays(90))->delete();
AnalyticsError::where('created_at', '<', now()->subDays(30))->delete();
```

---

## Security Considerations

1. **Rate Limiting** - Implement rate limits on analytics endpoints
2. **Validation** - Validate all incoming data
3. **CORS** - Configure CORS to accept requests from your Next.js domain
4. **Authentication** - Consider adding API key authentication for production
5. **Data Sanitization** - Sanitize user agent and URL data to prevent XSS

---

## Monitoring

Monitor these aspects of the analytics system:

1. **Endpoint Health** - Ensure analytics endpoints are responding
2. **Data Volume** - Track incoming request volume
3. **Error Rates** - Monitor failed analytics requests
4. **Database Performance** - Watch for slow queries on analytics tables
5. **Storage Growth** - Monitor database size growth

---

## Support

For questions or issues with the analytics integration, contact the development team.

Last Updated: April 22, 2026
