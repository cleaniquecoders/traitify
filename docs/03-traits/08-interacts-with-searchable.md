# InteractsWithSearchable Trait

A Laravel trait that adds case-insensitive search capabilities to your Eloquent models with support for searching across multiple fields.

## Overview

The InteractsWithSearchable trait provides a simple and efficient way to implement search functionality in your Laravel models using case-insensitive SQL LIKE queries.

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Basic Usage

### 1. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CleaniqueCoders\Traitify\Concerns\InteractsWithSearchable;

class Post extends Model
{
    use InteractsWithSearchable;

    protected $fillable = ['title', 'content', 'author'];
}
```

### 2. Use the Search Scope

```php
// Search in a single field
$posts = Post::search('title', 'laravel')->get();

// Search in multiple fields
$posts = Post::search(['title', 'content'], 'laravel')->get();
```

## Features

### Case-Insensitive Search

Search is automatically case-insensitive:

```php
// All of these will find "Laravel Tutorial"
Post::search('title', 'laravel')->get();
Post::search('title', 'LARAVEL')->get();
Post::search('title', 'LaRaVeL')->get();
```

### Partial Matching

Uses LIKE with wildcards for partial matching:

```php
// Finds: "Laravel Tutorial", "Getting Started with Laravel", "Laravel 10"
Post::search('title', 'laravel')->get();
```

### Multiple Field Search

Search across multiple fields simultaneously:

```php
// Searches in both title and content
$posts = Post::search(['title', 'content'], 'tutorial')->get();
```

### Chainable with Other Queries

Combine with other query methods:

```php
Post::search('title', 'laravel')
    ->where('status', 'published')
    ->orderBy('created_at', 'desc')
    ->paginate(10);
```

## Examples

### Simple Search

```php
class Post extends Model
{
    use InteractsWithSearchable;
}

// Search posts
$results = Post::search('title', 'laravel')->get();
```

### Multi-Field Search

```php
// Search in title OR content
$results = Post::search(['title', 'content'], 'tutorial')->get();
```

### Search with Filters

```php
$results = Post::search(['title', 'content'], $query)
    ->where('status', 'published')
    ->where('user_id', auth()->id())
    ->get();
```

### Paginated Search Results

```php
$results = Post::search(['title', 'content', 'author'], $searchTerm)
    ->paginate(15);
```

### Search with Relationships

```php
$posts = Post::with('user')
    ->search(['title', 'content'], $keyword)
    ->get();
```

## Advanced Examples

### Controller Implementation

```php
class PostController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->input('q');

        $posts = Post::search(['title', 'content', 'excerpt'], $keyword)
            ->where('status', 'published')
            ->orderBy('relevance', 'desc')
            ->paginate(20);

        return view('posts.search', compact('posts', 'keyword'));
    }
}
```

### API Endpoint

```php
Route::get('/api/posts/search', function (Request $request) {
    $query = $request->input('q');

    $posts = Post::search(['title', 'content'], $query)
        ->select('id', 'title', 'slug', 'excerpt')
        ->limit(10)
        ->get();

    return response()->json($posts);
});
```

### Searchable Interface

```php
interface Searchable
{
    public function getSearchableFields(): array;
}

class Post extends Model implements Searchable
{
    use InteractsWithSearchable;

    public function getSearchableFields(): array
    {
        return ['title', 'content', 'excerpt'];
    }
}

// Usage
$posts = Post::search(
    $post->getSearchableFields(),
    $keyword
)->get();
```

### Building a Search Form

```blade
{{-- search.blade.php --}}
<form action="{{ route('posts.search') }}" method="GET">
    <input type="text"
           name="q"
           value="{{ request('q') }}"
           placeholder="Search posts..."
           class="form-control">
    <button type="submit">Search</button>
</form>

@if(request('q'))
    <p>Found {{ $posts->total() }} results for "{{ request('q') }}"</p>

    @foreach($posts as $post)
        <article>
            <h2>{{ $post->title }}</h2>
            <p>{{ $post->excerpt }}</p>
        </article>
    @endforeach

    {{ $posts->links() }}
@endif
```

### Highlighting Search Results

```php
class Post extends Model
{
    use InteractsWithSearchable;

    public function getHighlightedTitle($keyword)
    {
        return $this->highlight($this->title, $keyword);
    }

    protected function highlight($text, $keyword)
    {
        if (empty($keyword)) {
            return $text;
        }

        return preg_replace(
            '/(' . preg_quote($keyword, '/') . ')/iu',
            '<mark>$1</mark>',
            $text
        );
    }
}

// In view
{!! $post->getHighlightedTitle($keyword) !!}
```

## Methods Reference

### Query Scopes

| Scope | Parameters | Description |
|-------|-----------|-------------|
| `search($fields, $keyword)` | `string\|array` `$fields`<br>`string` `$keyword` | Search specified fields for keyword |

## Performance Considerations

### 1. Add Indexes for Better Performance

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->timestamps();

    // Add indexes on searchable columns
    $table->index('title');
    $table->fullText(['title', 'content']); // For better performance
});
```

### 2. Use Full-Text Search for Large Datasets

For better performance on large datasets, consider MySQL/PostgreSQL full-text search:

```php
// Migration
$table->fullText(['title', 'content']);

// Model
public function scopeFullTextSearch($query, $keyword)
{
    return $query->whereRaw(
        'MATCH(title, content) AGAINST(? IN BOOLEAN MODE)',
        [$keyword . '*']
    );
}
```

### 3. Limit Searchable Fields

```php
// Good - specific fields
$posts = Post::search(['title', 'excerpt'], $keyword)->get();

// Avoid - too many fields
$posts = Post::search([
    'title', 'content', 'excerpt', 'meta', 'tags', 'author', 'category'
], $keyword)->get();
```

### 4. Use Pagination

```php
// Good - paginated results
$posts = Post::search($fields, $keyword)->paginate(20);

// Avoid - loading all results
$posts = Post::search($fields, $keyword)->get();
```

## Integration with Laravel Scout

For more advanced search features, consider upgrading to Laravel Scout:

```php
use Laravel\Scout\Searchable;

class Post extends Model
{
    use Searchable;

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }
}

// Usage
$posts = Post::search('laravel')->get();
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InteractsWithSearchableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_search_by_title()
    {
        Post::factory()->create(['title' => 'Laravel Tutorial']);
        Post::factory()->create(['title' => 'Vue.js Guide']);

        $results = Post::search('title', 'laravel')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Laravel Tutorial', $results->first()->title);
    }

    /** @test */
    public function search_is_case_insensitive()
    {
        Post::factory()->create(['title' => 'Laravel Tutorial']);

        $results = Post::search('title', 'LARAVEL')->get();

        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_searches_multiple_fields()
    {
        Post::factory()->create([
            'title' => 'Introduction',
            'content' => 'This is a Laravel tutorial',
        ]);

        $results = Post::search(['title', 'content'], 'laravel')->get();

        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_finds_partial_matches()
    {
        Post::factory()->create(['title' => 'Getting Started with Laravel']);

        $results = Post::search('title', 'started')->get();

        $this->assertCount(1, $results);
    }
}
```

## Best Practices

### 1. Validate Search Input

```php
public function search(Request $request)
{
    $validated = $request->validate([
        'q' => 'required|string|min:2|max:100',
    ]);

    $posts = Post::search(['title', 'content'], $validated['q'])
        ->paginate(20);

    return view('posts.search', compact('posts'));
}
```

### 2. Sanitize Search Keywords

```php
public function search(Request $request)
{
    $keyword = strip_tags($request->input('q'));
    $keyword = htmlspecialchars($keyword);

    $posts = Post::search(['title', 'content'], $keyword)->get();

    return view('posts.search', compact('posts', 'keyword'));
}
```

### 3. Set Minimum Search Length

```php
public function search(Request $request)
{
    $keyword = $request->input('q');

    if (strlen($keyword) < 3) {
        return back()->withErrors(['q' => 'Search must be at least 3 characters']);
    }

    $posts = Post::search(['title', 'content'], $keyword)->paginate(20);

    return view('posts.search', compact('posts'));
}
```

### 4. Cache Popular Searches

```php
use Illuminate\Support\Facades\Cache;

public function search(Request $request)
{
    $keyword = $request->input('q');

    $results = Cache::remember("search:{$keyword}", 3600, function () use ($keyword) {
        return Post::search(['title', 'content'], $keyword)
            ->published()
            ->get();
    });

    return view('posts.search', compact('results'));
}
```

## Troubleshooting

### No Results Found

**Problem**: Search returns empty results even when data exists.

**Solutions**:
1. Check field names are correct
2. Verify data exists in database
3. Check for typos in search keyword
4. Ensure fields contain text (not null)

### Special Characters Issues

**Problem**: Search fails with special characters.

**Solution**: Sanitize input:

```php
$keyword = preg_replace('/[^A-Za-z0-9\s]/', '', $keyword);
```

### Performance Issues

**Problem**: Search is slow on large datasets.

**Solutions**:
1. Add database indexes
2. Use full-text search
3. Implement pagination
4. Consider Laravel Scout
5. Cache results

## Next Steps

- [InteractsWithDetails](09-interacts-with-details.md) - Eager loading helpers
- [InteractsWithApi](10-interacts-with-api.md) - API response formatting
- [Examples](../06-examples/README.md) - Real-world usage examples
