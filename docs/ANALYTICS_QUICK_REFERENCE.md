# Analytics Quick Reference

## Backend Endpoints Summary

| Endpoint | Method | Purpose | Key Fields |
|----------|--------|---------|------------|
| `/analytics/event` | POST | Track user interactions | `name`, `properties`, `sessionId`, `timestamp` |
| `/analytics/pageload` | POST | Track page performance | `url`, `loadTime`, `timestamp` |
| `/analytics/error` | POST | Log JavaScript errors | `message`, `severity`, `timestamp` |

---

## Data Flow

```
Frontend (Next.js)
    ↓
    ├─→ Local API (/api/analytics/*) → JSON files (logs/)
    └─→ Backend API (Laravel) → Database
```

---

## Event Types Reference

### Page View
```json
{
  "name": "page_view",
  "properties": { "page": "/academics" }
}
```

### Button Click
```json
{
  "name": "button_click",
  "properties": { 
    "button": "Apply Now",
    "location": "Hero Section"
  }
}
```

### Form Submission
```json
{
  "name": "form_submission",
  "properties": { 
    "form": "Contact Form",
    "success": true
  }
}
```

---

## Required Laravel Routes

```php
// routes/api.php
Route::prefix('v1/analytics')->group(function () {
    Route::post('/event', [AnalyticsController::class, 'storeEvent']);
    Route::post('/pageload', [AnalyticsController::class, 'storePageLoad']);
    Route::post('/error', [AnalyticsController::class, 'storeError']);
});
```

---

## Environment Setup

```bash
# .env.local (Next.js)
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1

# Production
NEXT_PUBLIC_API_URL=https://api.lgihe.ac.ug/api/v1
```

---

## Testing Commands

```bash
# Test event tracking
curl -X POST http://localhost:8000/api/v1/analytics/event \
  -H "Content-Type: application/json" \
  -d '{"name":"page_view","properties":{"page":"/test"},"timestamp":"2026-04-22T10:30:00.000Z"}'

# Test page load tracking
curl -X POST http://localhost:8000/api/v1/analytics/pageload \
  -H "Content-Type: application/json" \
  -d '{"url":"/test","loadTime":1500,"timestamp":"2026-04-22T10:30:00.000Z"}'

# Test error logging
curl -X POST http://localhost:8000/api/v1/analytics/error \
  -H "Content-Type: application/json" \
  -d '{"message":"Test error","severity":"low","timestamp":"2026-04-22T10:30:00.000Z"}'
```

---

## Privacy Compliance

| Consent Type | Controls | Default |
|--------------|----------|---------|
| Analytics | Event tracking | User choice |
| Performance | Page load metrics | User choice |
| Geolocation | Country/city data | User choice |

**Note**: Dashboard routes (`/dashboard/*`) are never tracked.

---

## Common Dashboard Queries

### Total Page Views (Last 30 Days)
```sql
SELECT COUNT(*) FROM analytics_events 
WHERE name = 'page_view' 
AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### Average Load Time by Page
```sql
SELECT url, AVG(load_time) as avg_load_time, COUNT(*) as views
FROM analytics_pageloads
GROUP BY url
ORDER BY views DESC
LIMIT 10;
```

### Error Rate
```sql
SELECT 
  (SELECT COUNT(*) FROM analytics_errors WHERE DATE(timestamp) = CURDATE()) as errors,
  (SELECT COUNT(*) FROM analytics_events WHERE DATE(timestamp) = CURDATE()) as events,
  ROUND((errors / events) * 100, 2) as error_rate_percent;
```

### Top Countries
```sql
SELECT country, country_code, COUNT(*) as visits
FROM analytics_events
WHERE country IS NOT NULL
GROUP BY country, country_code
ORDER BY visits DESC
LIMIT 10;
```

### Active Sessions (Last 5 Minutes)
```sql
SELECT COUNT(DISTINCT session_id) as active_visitors
FROM analytics_events
WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 5 MINUTE);
```

---

## Troubleshooting

### Analytics not appearing in backend?
1. Check `NEXT_PUBLIC_API_URL` is set correctly
2. Verify CORS is configured on Laravel backend
3. Check browser console for failed requests
4. Verify Laravel routes are registered

### High error rates?
1. Check `analytics_errors` table for patterns
2. Group by `message` to find common issues
3. Filter by `severity = 'critical'` for urgent issues

### Slow dashboard queries?
1. Add indexes on `timestamp`, `session_id`, `name`
2. Implement data archiving for old records
3. Use materialized views for complex aggregations

---

## Next Steps

1. ✅ Set `NEXT_PUBLIC_API_URL` in `.env.local`
2. ✅ Create Laravel migrations for analytics tables
3. ✅ Implement Laravel controller and routes
4. ✅ Test endpoints with curl commands
5. ✅ Build Laravel dashboard views
6. ✅ Configure CORS on Laravel backend
7. ✅ Set up data retention policies
8. ✅ Implement rate limiting

---

Last Updated: April 22, 2026
