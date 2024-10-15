<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Mock the model with the InteractsWithSearchable trait
class SearchableTestModel extends Model
{
    use \CleaniqueCoders\Traitify\Concerns\InteractsWithSearchable;

    protected $table = 'searchable_test_models';

    protected $fillable = ['name', 'description'];
}

beforeEach(function () {
    // Create the test table
    Schema::create('searchable_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description');
        $table->timestamps();
    });

    // Seed some sample data
    SearchableTestModel::create([
        'name' => 'First Test Model',
        'description' => 'This is the first test model description.',
    ]);

    SearchableTestModel::create([
        'name' => 'Second Test Model',
        'description' => 'A completely different test model.',
    ]);

    SearchableTestModel::create([
        'name' => 'Third Model',
        'description' => 'This one is unique.',
    ]);
});

afterEach(function () {
    Schema::dropIfExists('searchable_test_models');
});

it('can search using a single field', function () {
    $results = SearchableTestModel::search('name', 'first')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('First Test Model');
});

it('can search using multiple fields', function () {
    $results = SearchableTestModel::search(['name', 'description'], 'unique')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Third Model');
});

it('performs case-insensitive search', function () {
    $results = SearchableTestModel::search('description', 'COMPLETELY')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Second Test Model');
});

it('returns no results when there is no match', function () {
    $results = SearchableTestModel::search('name', 'nonexistent')->get();

    expect($results)->toHaveCount(0);
});
