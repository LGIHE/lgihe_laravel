# Widget Fixes - Table Record Key Issue

## Issue
When loading the dashboard, you encountered the error:
```
Filament\Widgets\TableWidget::getTableRecordKey(): Return value must be of type string, null returned
```

## Root Cause
Filament's TableWidget requires each table row to have a unique string key. When using aggregated queries (with `GROUP BY`), the results don't have an `id` field from the database, causing the default `getTableRecordKey()` method to return `null`.

## Solution
Added custom `getTableRecordKey()` methods to all table widgets that use aggregated data.

---

## Widgets Fixed

### 1. TopPagesWidget
**Problem**: Aggregated query groups by page URL, no `id` field.

**Solution**: 
```php
public function getTableRecordKey($record): string
{
    // Use page as unique key, or generate one
    $page = $record->page ?? 'unknown';
    return md5($page . '_' . ($record->views ?? 0));
}
```

### 2. TrafficByCountryWidget
**Problem**: Aggregated query groups by country, no `id` field.

**Solution**:
```php
public function getTableRecordKey($record): string
{
    // Use country_code as unique key
    return $record->country_code ?? md5($record->country ?? uniqid());
}
```

### 3. RecentErrorsWidget
**Problem**: While this widget doesn't use aggregation, it needed the method to be public.

**Solution**:
```php
public function getTableRecordKey($record): string
{
    return (string) ($record->id ?? uniqid());
}
```

---

## Additional Improvements

### Disabled Pagination
Added `->paginated(false)` to all table widgets since they already limit results to 10-20 rows. This improves performance and simplifies the UI.

### SQLite Compatibility
Removed `ROW_NUMBER()` window function which isn't supported in older SQLite versions. Instead, we generate unique keys using:
- Natural keys (country_code, page URL)
- MD5 hashes for composite keys
- `uniqid()` as fallback

---

## Testing

### Verify Dashboard Loads
1. Navigate to `/admin`
2. Login to admin panel
3. Dashboard should display without errors

### Expected Behavior
- **With No Data**: Widgets show "No records found" or empty tables
- **With Data**: Widgets display analytics data correctly

### Test with Sample Data
```bash
php artisan tinker
```

```php
// Create sample event
App\Models\AnalyticsEvent::create([
    'name' => 'page_view',
    'properties' => ['page' => '/test'],
    'session_id' => 'test_session_123',
    'timestamp' => now(),
]);

// Create sample error
App\Models\AnalyticsError::create([
    'message' => 'Test error',
    'severity' => 'low',
    'timestamp' => now(),
]);

// Create sample page load
App\Models\PageLoad::create([
    'url' => '/test',
    'load_time' => 1500,
    'session_id' => 'test_session_123',
    'timestamp' => now(),
]);
```

Then refresh the dashboard to see the data.

---

## Key Takeaways

### When to Override getTableRecordKey()
Override this method when:
- Using aggregated queries (`GROUP BY`)
- Using raw SQL queries without an `id` field
- Working with non-Eloquent data sources

### Best Practices
1. **Use Natural Keys**: If your data has a unique field (like country_code), use it
2. **Generate Stable Keys**: Use MD5 or similar for composite keys
3. **Fallback to uniqid()**: Only as last resort for truly unique data
4. **Make it Public**: The method must be `public`, not `protected`

### Performance Considerations
- Disable pagination for small result sets (< 50 rows)
- Limit query results at the database level
- Use indexes on frequently queried columns

---

## Related Documentation
- [SQLite Compatibility Notes](./SQLITE_COMPATIBILITY_NOTES.md)
- [Implementation Summary](./IMPLEMENTATION_SUMMARY.md)

---

**Last Updated**: April 22, 2026
