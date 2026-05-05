# 📊 Analytics Integration Guide

Complete guide for the LGIHE Analytics system with Filament widgets.

## 🎯 Overview

The analytics system tracks website usage, user behavior, and system performance through a custom analytics solution integrated with Filament dashboard widgets.

### Key Features

- ✅ Page view tracking
- ✅ Error logging and monitoring
- ✅ Performance metrics (page load times)
- ✅ Geographic data (country-based traffic)
- ✅ Real-time dashboard widgets
- ✅ Custom event tracking
- ✅ API endpoints for data collection

## 📊 Dashboard Widgets

### Analytics Overview Widget

**Location**: Admin Dashboard (top)

**Metrics Displayed**:
- **Total Page Views**: Last 30 days
- **Unique Visitors**: Estimated unique users
- **Avg. Load Time**: Average page load time in ms
- **Error Rate**: Percentage of errors vs total requests

**Features**:
- Trend indicators (up/down from previous period)
- Color-coded performance indicators
- Click to view detailed reports

### Top Pages Widget

**Location**: Admin Dashboard

**Shows**:
- Most visited pages (top 10)
- Page view counts
- Percentage of total traffic
- Trend over time

### Traffic by Country Widget

**Location**: Admin Dashboard

**Shows**:
- Geographic distribution of visitors
- Country names and flags
- Visitor counts per country
- Interactive map (optional)

### Recent Errors Widget

**Location**: Admin Dashboard

**Shows**:
- Latest error messages
- Error timestamps
- Affected pages
- Error frequency
- Quick links to investigate

## 🚀 Quick Start

### Enable Analytics

1. **Run Migrations**:
```bash
php artisan migrate
```

2. **Access Dashboard**:
   - Login to admin panel
   - Navigate to Dashboard
   - View analytics widgets

3. **Grant Permissions**:
```bash
# Assign view_analytics permission to roles
php artisan tinker
>>> $role = Role::where('name', 'Admin')->first();
>>> $role->givePermissionTo('view_analytics');
```

### Frontend Integration

Add to your frontend application:

```javascript
// Track page view
fetch('https://api.lgihe.org/api/v1/analytics/event', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    event_name: 'page_view',
    event_data: {
      page: window.location.pathname,
      referrer: document.referrer,
      title: document.title
    },
    user_agent: navigator.userAgent,
    ip_address: null // Server will capture
  })
});

// Track custom event
fetch('https://api.lgihe.org/api/v1/analytics/event', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    event_name: 'button_click',
    event_data: {
      button_id: 'apply-now',
      page: '/admissions'
    }
  })
});

// Log error
window.addEventListener('error', (event) => {
  fetch('https://api.lgihe.org/api/v1/analytics/error', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      error_message: event.message,
      error_stack: event.error?.stack,
      page_url: window.location.href,
      user_agent: navigator.userAgent
    })
  });
});

// Track page load time
window.addEventListener('load', () => {
  const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
  
  fetch('https://api.lgihe.org/api/v1/analytics/pageload', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      page_url: window.location.href,
      load_time: loadTime,
      user_agent: navigator.userAgent
    })
  });
});
```

## 💻 API Reference

### Track Event

```http
POST /api/v1/analytics/event
Content-Type: application/json

{
  "event_name": "page_view",
  "event_data": {
    "page": "/about",
    "referrer": "https://google.com",
    "title": "About Us"
  },
  "user_agent": "Mozilla/5.0...",
  "ip_address": "192.168.1.1" // Optional, auto-captured
}
```

**Response**:
```json
{
  "success": true,
  "message": "Event tracked successfully"
}
```

### Log Error

```http
POST /api/v1/analytics/error
Content-Type: application/json

{
  "error_message": "Failed to load resource",
  "error_stack": "Error: Failed to load...\n  at...",
  "page_url": "/contact",
  "user_agent": "Mozilla/5.0..."
}
```

### Track Page Load

```http
POST /api/v1/analytics/pageload
Content-Type: application/json

{
  "page_url": "/about",
  "load_time": 1234,
  "user_agent": "Mozilla/5.0..."
}
```

### Get Analytics Data (Admin)

```http
GET /api/v1/admin/analytics/overview
Authorization: Bearer {token}

Query Parameters:
- from_date (date, default: 30 days ago)
- to_date (date, default: today)
```

**Response**:
```json
{
  "total_page_views": 15234,
  "unique_visitors": 3421,
  "avg_load_time": 1234,
  "error_rate": 0.02,
  "top_pages": [
    {"page": "/", "views": 5234},
    {"page": "/about", "views": 2341}
  ],
  "traffic_by_country": [
    {"country": "Lesotho", "visitors": 2100},
    {"country": "South Africa", "visitors": 890}
  ]
}
```

## 🎨 Customizing Widgets

### Modify Widget Data

Edit widget files in `app/Filament/Widgets/`:

```php
// app/Filament/Widgets/AnalyticsOverviewWidget.php

protected function getStats(): array
{
    $from = now()->subDays(30);
    $to = now();
    
    return [
        Stat::make('Total Page Views', $this->getPageViews($from, $to))
            ->description('Last 30 days')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success'),
        
        // Add more stats...
    ];
}

protected function getPageViews($from, $to)
{
    return AnalyticsEvent::where('event_name', 'page_view')
        ->whereBetween('created_at', [$from, $to])
        ->count();
}
```

### Change Widget Order

Edit `app/Filament/Pages/Dashboard.php`:

```php
public function getWidgets(): array
{
    return [
        AnalyticsOverviewWidget::class,
        TopPagesWidget::class,
        TrafficByCountryWidget::class,
        RecentErrorsWidget::class,
    ];
}
```

### Hide Widgets for Roles

```php
// In widget class
public static function canView(): bool
{
    return auth()->user()->can('view_analytics');
}
```

## 📈 Analytics Best Practices

### Data Collection

1. **Respect Privacy**: Don't track personal information
2. **Anonymize IPs**: Hash or truncate IP addresses
3. **Cookie Consent**: Implement cookie consent banner
4. **GDPR Compliance**: Allow users to opt-out
5. **Data Retention**: Delete old data after 90 days

### Performance

1. **Async Tracking**: Use async requests for tracking
2. **Batch Events**: Send multiple events in one request
3. **Cache Results**: Cache widget data for 5-10 minutes
4. **Index Database**: Ensure proper indexes on analytics tables
5. **Archive Old Data**: Move old data to archive tables

### Security

1. **Rate Limiting**: Limit tracking requests per IP
2. **Validate Input**: Sanitize all tracking data
3. **Prevent Spam**: Implement bot detection
4. **Secure API**: Use CORS and API keys if needed
5. **Monitor Abuse**: Alert on unusual patterns

## 🔧 Troubleshooting

### Widgets Not Showing Data

**Problem**: Dashboard widgets show zero or no data

**Solution**:
```bash
# Check if events are being recorded
php artisan tinker
>>> AnalyticsEvent::count()
>>> AnalyticsEvent::latest()->first()

# Check permissions
>>> auth()->user()->can('view_analytics')

# Clear cache
php artisan cache:clear
```

### High Database Load

**Problem**: Analytics queries slowing down database

**Solution**:
```bash
# Add indexes
php artisan migrate

# Cache widget data
# Edit widget and add caching:
Cache::remember('analytics_overview', 300, function() {
    return $this->getStats();
});

# Archive old data
php artisan analytics:archive --days=90
```

### Tracking Not Working

**Problem**: Events not being recorded from frontend

**Solution**:
```bash
# Check CORS configuration
# In config/cors.php, ensure frontend URL is allowed

# Check API logs
tail -f storage/logs/laravel.log

# Test endpoint
curl -X POST https://api.lgihe.org/api/v1/analytics/event \
  -H "Content-Type: application/json" \
  -d '{"event_name":"test","event_data":{}}'
```

## 📊 Advanced Features

### Custom Event Types

Track custom events for your application:

```javascript
// Track form submission
trackEvent('form_submit', {
  form_name: 'contact_form',
  success: true
});

// Track download
trackEvent('file_download', {
  file_name: 'prospectus.pdf',
  file_size: 2048576
});

// Track video play
trackEvent('video_play', {
  video_id: 'intro-video',
  duration: 120
});
```

### Conversion Tracking

Track user journeys and conversions:

```javascript
// Track application start
trackEvent('application_started', {
  programme: 'Computer Science'
});

// Track application completion
trackEvent('application_completed', {
  programme: 'Computer Science',
  time_taken: 1200 // seconds
});
```

### A/B Testing

Track experiment variants:

```javascript
trackEvent('experiment_view', {
  experiment_id: 'homepage_v2',
  variant: 'B'
});
```

## 📚 Additional Resources

- [Google Analytics Alternative](https://plausible.io/)
- [Privacy-Focused Analytics](https://usefathom.com/)
- [Filament Widgets Documentation](https://filamentphp.com/docs/3.x/widgets)

---

**Version**: 1.0.0  
**Last Updated**: May 5, 2026  
**Status**: Production Ready ✅
