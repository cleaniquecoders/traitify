<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Mock the model with the InteractsWithMeta trait
class MetaTestModel extends Model
{
    use \CleaniqueCoders\Traitify\Concerns\InteractsWithMeta;

    protected $table = 'meta_test_models';

    protected $fillable = ['name', 'meta'];

    public $casts = ['meta' => 'array'];
}

beforeEach(function () {
    // Create the test table
    Schema::create('meta_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->json('meta')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('meta_test_models');
});

it('automatically adds meta cast and default value when creating a model', function () {
    $model = MetaTestModel::create(['name' => 'Test Model']);

    // Check if 'meta' is cast to an array and contains the default value
    expect($model->getCasts())->toHaveKey('meta', 'array')
        ->and($model->meta)->toBeArray()->toBeEmpty();
});

it('does not overwrite existing meta value when creating a model', function () {
    $existingMeta = ['key' => 'value'];

    // Create a model with predefined meta data without manually encoding the array
    $model = MetaTestModel::create([
        'name' => 'Test Model',
        'meta' => $existingMeta, // Let Laravel handle casting to JSON
    ]);

    expect($model->meta)->toBeArray()->toMatchArray($existingMeta);
});

it('returns default meta if not set', function () {
    $model = new class extends MetaTestModel
    {
        public $default_meta = ['default_key' => 'default_value'];
    };

    $model->name = 'Test Model'; // Provide a name
    $model->save();

    expect($model->meta)->toBeArray()->toMatchArray(['default_key' => 'default_value']);
});

it('uses default empty array for meta if no default_meta property is defined', function () {
    $model = MetaTestModel::create(['name' => 'Test Model']);

    expect($model->meta)->toBeArray()->toBeEmpty();
});
