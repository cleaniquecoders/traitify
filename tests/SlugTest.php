<?php

use CleaniqueCoders\Traitify\Concerns\InteractsWithSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

// Mock the model with the InteractsWithSlug trait
class SlugTestModel extends Model
{
    use InteractsWithSlug;

    protected $table = 'slug_test_models';

    protected $fillable = ['name', 'slug', 'title'];
}

// Mock model with custom slug column configuration
class CustomSlugTestModel extends Model
{
    use InteractsWithSlug;

    protected $table = 'custom_slug_test_models';

    protected $fillable = ['title', 'custom_slug'];

    protected $slug_column = 'custom_slug';

    protected $slug_source_column = 'title';
}

// Create the tables for testing
beforeEach(function () {
    Schema::create('slug_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->nullable();
        $table->string('title')->nullable();
        $table->timestamps();
    });

    Schema::create('custom_slug_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('custom_slug')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('slug_test_models');
    Schema::dropIfExists('custom_slug_test_models');
});

it('generates a slug when creating a model', function () {
    $model = SlugTestModel::create(['name' => 'Test Model Name']);

    expect($model->slug)->toBe('test-model-name');
});

it('does not overwrite existing slug when creating', function () {
    $model = SlugTestModel::create(['name' => 'Test Model', 'slug' => 'custom-slug']);

    expect($model->slug)->toBe('custom-slug');
});

it('generates slug with special characters', function () {
    $model = SlugTestModel::create(['name' => 'Test & Model! With @Special# Characters']);

    expect($model->slug)->toBe('test-model-with-at-special-characters');
});

it('does not generate slug when source column is empty', function () {
    $model = SlugTestModel::create(['name' => '']);

    expect($model->slug)->toBeNull();
});

it('generates slug when updating model if slug is empty', function () {
    $model = SlugTestModel::create(['name' => 'Initial Name']);
    $originalSlug = $model->slug;

    // Update with new name but slug still empty
    $model->update(['name' => 'Updated Name', 'slug' => null]);

    expect($model->slug)->toBe('updated-name')
        ->and($model->slug)->not->toBe($originalSlug);
});

it('does not update slug when updating if slug already exists', function () {
    $model = SlugTestModel::create(['name' => 'Initial Name']);
    $originalSlug = $model->slug;

    // Update name but keep existing slug
    $model->update(['name' => 'Updated Name']);

    expect($model->slug)->toBe($originalSlug);
});

it('updates slug when source column changes and slug is empty', function () {
    $model = SlugTestModel::create(['name' => 'Initial Name']);

    // Clear the slug and update the name
    $model->slug = null;
    $model->name = 'New Name';
    $model->save();

    expect($model->slug)->toBe('new-name');
});

it('returns the correct slug column name', function () {
    $model = new SlugTestModel;

    // Default behavior
    expect($model->getSlugColumnName())->toBe('slug');

    // Custom behavior
    $model->slug_column = 'custom_slug';
    expect($model->getSlugColumnName())->toBe('custom_slug');
});

it('returns the correct slug source column name', function () {
    $model = new SlugTestModel;

    // Default behavior
    expect($model->getSlugSourceColumnName())->toBe('name');

    // Custom behavior
    $model->slug_source_column = 'title';
    expect($model->getSlugSourceColumnName())->toBe('title');
});

it('can query models by slug using scope', function () {
    $model = SlugTestModel::create(['name' => 'Test Model']);

    $foundModel = SlugTestModel::slug('test-model')->first();

    expect($foundModel)->not->toBeNull()
        ->and($foundModel->id)->toBe($model->id)
        ->and($foundModel->slug)->toBe('test-model');
});

it('works with custom slug column configuration', function () {
    $model = CustomSlugTestModel::create(['title' => 'Custom Test Title']);

    expect($model->custom_slug)->toBe('custom-test-title');
});

it('can query custom slug models by slug', function () {
    $model = CustomSlugTestModel::create(['title' => 'Custom Test']);

    $foundModel = CustomSlugTestModel::slug('custom-test')->first();

    expect($foundModel)->not->toBeNull()
        ->and($foundModel->id)->toBe($model->id)
        ->and($foundModel->custom_slug)->toBe('custom-test');
});

it('handles unicode characters in slug generation', function () {
    $model = SlugTestModel::create(['name' => 'Test with ñoño and café']);

    expect($model->slug)->toBe('test-with-nono-and-cafe');
});

it('handles empty string as different from null', function () {
    $model = SlugTestModel::create(['name' => 'Test Model', 'slug' => '']);

    // Empty string should allow slug generation
    expect($model->slug)->toBe('test-model');
});

it('does not generate slug when column does not exist', function () {
    // Create a model without slug column
    Schema::create('no_slug_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    $model = new class extends Model
    {
        use InteractsWithSlug;

        protected $table = 'no_slug_models';

        protected $fillable = ['name'];
    };

    // This should not throw an error
    $createdModel = $model::create(['name' => 'Test']);
    expect($createdModel->name)->toBe('Test');

    Schema::dropIfExists('no_slug_models');
});
