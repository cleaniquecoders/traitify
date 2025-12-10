# InteractsWithSqlViewMigration Trait

A Laravel trait that simplifies the management of SQL view migrations by reading SQL files and executing them during migrations.

## Overview

The InteractsWithSqlViewMigration trait makes it easy to create and manage database views in Laravel by storing SQL in separate files and executing them through migrations.

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Setup

### 1. Create SQL Directory

```bash
mkdir -p database/sql
```

### 2. Create SQL Files

Create two SQL files for creating and dropping views:

**database/sql/create-sql-views.sql:**
```sql
CREATE OR REPLACE VIEW user_stats AS
SELECT
    users.id,
    users.name,
    COUNT(DISTINCT posts.id) as posts_count,
    COUNT(DISTINCT comments.id) as comments_count
FROM users
LEFT JOIN posts ON posts.user_id = users.id
LEFT JOIN comments ON comments.user_id = users.id
GROUP BY users.id, users.name;
```

**database/sql/drop-sql-views.sql:**
```sql
DROP VIEW IF EXISTS user_stats;
```

### 3. Create Migration

```bash
php artisan make:migration create_user_stats_view
```

### 4. Use the Trait in Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use CleaniqueCoders\Traitify\Concerns\InteractsWithSqlViewMigration;

class CreateUserStatsView extends Migration
{
    use InteractsWithSqlViewMigration;

    // That's it! The trait handles up() and down()
}
```

## Features

### Automatic SQL File Execution

The trait automatically:
- Reads SQL from `database/sql/create-sql-views.sql` on `up()`
- Reads SQL from `database/sql/drop-sql-views.sql` on `down()`
- Executes each statement separately

### Custom File Names

Override default file names:

```php
class CreateProductStatsView extends Migration
{
    use InteractsWithSqlViewMigration;

    protected $up_filename = 'create-product-stats.sql';
    protected $down_filename = 'drop-product-stats.sql';
}
```

### Custom Storage Path

Override the default SQL directory:

```php
class CreateOrderStatsView extends Migration
{
    use InteractsWithSqlViewMigration;

    protected function getStoragePath(): string
    {
        return database_path('views');
    }
}
```

## Examples

### Simple View

**Migration:**
```php
class CreateActiveUsersView extends Migration
{
    use InteractsWithSqlViewMigration;

    protected $up_filename = 'create-active-users-view.sql';
    protected $down_filename = 'drop-active-users-view.sql';
}
```

**database/sql/create-active-users-view.sql:**
```sql
CREATE OR REPLACE VIEW active_users AS
SELECT *
FROM users
WHERE last_login_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
AND deleted_at IS NULL;
```

**database/sql/drop-active-users-view.sql:**
```sql
DROP VIEW IF EXISTS active_users;
```

### Complex Aggregated View

**database/sql/create-post-stats.sql:**
```sql
CREATE OR REPLACE VIEW post_stats AS
SELECT
    posts.id,
    posts.title,
    posts.published_at,
    users.name as author_name,
    COUNT(DISTINCT comments.id) as comments_count,
    COUNT(DISTINCT likes.id) as likes_count,
    AVG(ratings.value) as average_rating
FROM posts
INNER JOIN users ON users.id = posts.user_id
LEFT JOIN comments ON comments.post_id = posts.id
LEFT JOIN likes ON likes.likeable_id = posts.id AND likes.likeable_type = 'App\\Models\\Post'
LEFT JOIN ratings ON ratings.post_id = posts.id
WHERE posts.status = 'published'
GROUP BY posts.id, posts.title, posts.published_at, users.name;
```

### Multiple Views in One Migration

**database/sql/create-analytics-views.sql:**
```sql
-- User statistics view
CREATE OR REPLACE VIEW user_statistics AS
SELECT
    id,
    name,
    email,
    created_at,
    (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as total_posts,
    (SELECT COUNT(*) FROM comments WHERE comments.user_id = users.id) as total_comments
FROM users;

-- Post statistics view
CREATE OR REPLACE VIEW post_statistics AS
SELECT
    posts.id,
    posts.title,
    COUNT(DISTINCT comments.id) as comments,
    COUNT(DISTINCT likes.id) as likes,
    COUNT(DISTINCT shares.id) as shares
FROM posts
LEFT JOIN comments ON comments.post_id = posts.id
LEFT JOIN likes ON likes.post_id = posts.id
LEFT JOIN shares ON shares.post_id = posts.id
GROUP BY posts.id, posts.title;
```

**database/sql/drop-analytics-views.sql:**
```sql
DROP VIEW IF EXISTS user_statistics;
DROP VIEW IF EXISTS post_statistics;
```

### Materialized Views (PostgreSQL)

**database/sql/create-materialized-view.sql:**
```sql
CREATE MATERIALIZED VIEW IF NOT EXISTS daily_sales AS
SELECT
    DATE(created_at) as sale_date,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as average_order_value
FROM orders
WHERE status = 'completed'
GROUP BY DATE(created_at);

CREATE UNIQUE INDEX ON daily_sales (sale_date);
```

**database/sql/drop-materialized-view.sql:**
```sql
DROP MATERIALIZED VIEW IF EXISTS daily_sales;
```

## Using Views in Models

Create models for your views:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStats extends Model
{
    protected $table = 'user_stats';

    // Views are typically read-only
    public $incrementing = false;
    public $timestamps = false;

    // Prevent insert/update/delete
    public static function boot()
    {
        parent::boot();

        static::creating(function () {
            return false;
        });

        static::updating(function () {
            return false;
        });

        static::deleting(function () {
            return false;
        });
    }
}

// Usage
$stats = UserStats::find(1);
echo $stats->posts_count;
```

## Advanced Examples

### View with Indexes (MySQL)

```sql
CREATE OR REPLACE VIEW product_catalog AS
SELECT
    products.id,
    products.name,
    products.price,
    categories.name as category_name,
    brands.name as brand_name
FROM products
INNER JOIN categories ON categories.id = products.category_id
INNER JOIN brands ON brands.id = products.brand_id
WHERE products.is_active = 1;

-- Note: Regular views in MySQL cannot have indexes
-- Consider materialized views or indexed tables instead
```

### Refreshable Materialized View

**Model:**
```php
class DailySales extends Model
{
    protected $table = 'daily_sales';

    public static function refresh()
    {
        DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY daily_sales');
    }
}

// Usage - refresh view data
DailySales::refresh();
```

**Scheduled Refresh:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        DailySales::refresh();
    })->hourly();
}
```

## Methods Reference

### Migration Methods

| Method | Description |
|--------|-------------|
| `up()` | Creates views by executing create SQL file |
| `down()` | Drops views by executing drop SQL file |

### Protected Methods (Override if needed)

| Method | Returns | Description |
|--------|---------|-------------|
| `getUpFilename()` | `string` | Get filename for create SQL (default: 'create-sql-views.sql') |
| `getDownFilename()` | `string` | Get filename for drop SQL (default: 'drop-sql-views.sql') |
| `getStoragePath()` | `string` | Get SQL files directory (default: 'database/sql') |

## Best Practices

### 1. Use CREATE OR REPLACE VIEW

```sql
-- Good - won't fail if view exists
CREATE OR REPLACE VIEW user_stats AS ...

-- Avoid - will fail if view exists
CREATE VIEW user_stats AS ...
```

### 2. Always Use IF EXISTS in Drop

```sql
-- Good - won't fail if view doesn't exist
DROP VIEW IF EXISTS user_stats;

-- Avoid - will fail if view doesn't exist
DROP VIEW user_stats;
```

### 3. Order Views by Dependencies

```sql
-- If view_b depends on view_a, create view_a first
CREATE OR REPLACE VIEW view_a AS ...;
CREATE OR REPLACE VIEW view_b AS SELECT * FROM view_a WHERE ...;
```

### 4. Document View Purpose

```sql
-- Good - includes comments
/*
 * User Statistics View
 * Aggregates user activity metrics including posts and comments count
 * Used by: Dashboard, User Profile, Admin Reports
 */
CREATE OR REPLACE VIEW user_stats AS ...
```

### 5. Test View Performance

```sql
-- Add EXPLAIN to test view performance
EXPLAIN SELECT * FROM user_stats WHERE id = 1;
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SqlViewMigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_user_stats_view()
    {
        $this->artisan('migrate');

        $exists = DB::select("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_database = 'user_stats'");

        $this->assertNotEmpty($exists);
    }

    /** @test */
    public function user_stats_view_returns_correct_data()
    {
        $user = User::factory()
            ->has(Post::factory()->count(5))
            ->has(Comment::factory()->count(10))
            ->create();

        $stats = DB::table('user_stats')->where('id', $user->id)->first();

        $this->assertEquals(5, $stats->posts_count);
        $this->assertEquals(10, $stats->comments_count);
    }

    /** @test */
    public function it_drops_view_on_rollback()
    {
        $this->artisan('migrate');
        $this->artisan('migrate:rollback');

        $exists = DB::select("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_database = 'user_stats'");

        $this->assertEmpty($exists);
    }
}
```

## Troubleshooting

### SQL File Not Found

**Problem**: File not found: database/sql/create-sql-views.sql

**Solutions**:
1. Create the SQL directory: `mkdir -p database/sql`
2. Create the SQL file with proper name
3. Check file permissions

### View Already Exists Error

**Problem**: View 'user_stats' already exists.

**Solution**: Use `CREATE OR REPLACE VIEW`:
```sql
CREATE OR REPLACE VIEW user_stats AS ...
```

### Permission Denied

**Problem**: Access denied; you need the CREATE VIEW privilege.

**Solution**: Grant privileges to database user:
```sql
GRANT CREATE VIEW ON database.* TO 'user'@'localhost';
```

### Syntax Error in SQL

**Problem**: SQL syntax error when running migration.

**Solutions**:
1. Test SQL directly in database client first
2. Check for database-specific syntax
3. Ensure semicolons separate multiple statements

## Next Steps

- [Getting Started](../01-getting-started/README.md) - Basic usage
- [Examples](../06-examples/README.md) - Real-world examples
