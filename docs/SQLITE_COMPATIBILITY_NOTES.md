# SQLite Compatibility Notes

## Overview
The analytics widgets have been updated to work seamlessly with SQLite, MySQL, and PostgreSQL databases.

---

## Issues Fixed

### 1. JSON Functions
**Problem**: SQLite doesn't support MySQL's `JSON_UNQUOTE()` and `JSON_EXTRACT()` functions.

**Solution**: 
- SQLite uses: `json_extract(properties, '$.page')`
- MySQL uses: `JSON_UNQUOTE(JSON_EXTRACT(properties, "$.page"))`

The widgets now detect the database driver and use the appropriate syntax.

### 2. DATE Function
**Problem**: SQLite uses lowercase `date()` while MySQL uses uppercase `DATE()`.

**Solution**: Dynamic query building based on database driver.

### 3. DISTINCT COUNT
**Problem**: Laravel's `distinct()->count()` method can be unreliable with SQLite.

**Solution**: Use raw SQL `COUNT(DISTINCT column_name)` for better compatibility.

---

## Affected Widgets

### TopPagesWidget
- Detects database driver
- Uses appropriate JSON extraction syntax
- Trims quotes from SQLite JSON results

### AnalyticsOverviewWidget
- Uses `COUNT(DISTINCT session_id)` for unique visitor counts
- Uses lowercase `date()` for SQLite, uppercase `DATE()` for MySQL

### TrafficByCountryWidget
- Uses uppercase `COUNT()` for better cross-database compatibility

---

## Database Driver Detection

All widgets use this pattern:

```php
$driver = DB::connection()->getDriverName();

if ($driver === 'sqlite') {
    // SQLite-specific query
} else {
    // MySQL/PostgreSQL query
}
```

---

## Testing

### SQLite (Development)
```bash
php artisan db:show
# Should show: SQLite 3.x.x
```

### MySQL (Production)
Update `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### PostgreSQL (Alternative)
Update `.env`:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

---

## Migration Considerations

When migrating from SQLite to MySQL/PostgreSQL:

1. **Export Data**
   ```bash
   php artisan db:seed --class=ExportAnalyticsSeeder
   ```

2. **Update Database Configuration**
   ```bash
   # Update .env with new database credentials
   ```

3. **Run Migrations**
   ```bash
   php artisan migrate:fresh
   ```

4. **Import Data**
   ```bash
   php artisan db:seed --class=ImportAnalyticsSeeder
   ```

---

## Performance Notes

### SQLite
- ✅ Great for development
- ✅ No server setup required
- ⚠️ Limited concurrent writes
- ⚠️ Not recommended for high-traffic production

### MySQL
- ✅ Excellent for production
- ✅ Handles high concurrency
- ✅ Better performance with large datasets
- ✅ Advanced indexing options

### PostgreSQL
- ✅ Excellent for production
- ✅ Advanced JSON support
- ✅ Better for complex queries
- ✅ Strong data integrity

---

## Troubleshooting

### Error: "no such function: JSON_UNQUOTE"
**Cause**: Widget is using MySQL syntax on SQLite database.

**Fix**: Clear cache and ensure widgets are using the updated code:
```bash
php artisan optimize:clear
```

### Error: "no such function: DATE"
**Cause**: Case sensitivity issue with date function.

**Fix**: Widgets now handle this automatically. Clear cache:
```bash
php artisan optimize:clear
```

### Slow Query Performance
**Cause**: Missing indexes or large dataset.

**Fix**: 
1. Ensure indexes are created (check migration)
2. Implement data retention policy
3. Consider upgrading to MySQL/PostgreSQL

---

## Best Practices

### For Development
- Use SQLite for quick setup
- Test with sample data
- Monitor query performance

### For Production
- Use MySQL or PostgreSQL
- Set up proper indexes
- Implement data retention
- Monitor database size
- Set up regular backups

---

## Additional Resources

- [SQLite JSON Functions](https://www.sqlite.org/json1.html)
- [MySQL JSON Functions](https://dev.mysql.com/doc/refman/8.0/en/json-functions.html)
- [PostgreSQL JSON Functions](https://www.postgresql.org/docs/current/functions-json.html)

---

**Last Updated**: April 22, 2026
