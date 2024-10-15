<?php

use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

// Mock the model with the InteractsWithUuid trait
class UuidTestModel extends Model
{
    use InteractsWithUuid;

    protected $table = 'uuid_test_models';

    protected $fillable = ['name', 'uuid'];
}

// Create the table for testing
beforeEach(function () {
    Schema::create('uuid_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->uuid('uuid')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('uuid_test_models');
});

it('generates a UUID when creating a model', function () {
    $model = UuidTestModel::create(['name' => 'Test Model']);

    expect((string) $model->uuid)->toBeString()
        ->and(Str::isUuid((string) $model->uuid))->toBeTrue(); // Ensure it's a valid UUID
});

it('does not overwrite existing UUID', function () {
    $uuid = Str::orderedUuid();
    $model = UuidTestModel::create(['name' => 'Test Model', 'uuid' => $uuid]);

    expect($model->uuid)->toBe($uuid);
});

it('uses uuid column as route key name', function () {
    $model = new UuidTestModel;

    expect($model->getRouteKeyName())->toBe('uuid');
});

it('returns the correct uuid column name', function () {
    $model = new UuidTestModel;

    // Default behavior
    expect($model->getUuidColumnName())->toBe('uuid');

    // Custom behavior
    $model->uuid_column = 'custom_uuid';
    expect($model->getUuidColumnName())->toBe('custom_uuid');
});

it('can query models by uuid', function () {
    $uuid = Str::orderedUuid();
    UuidTestModel::create(['name' => 'Test Model', 'uuid' => $uuid]);

    $foundModel = UuidTestModel::uuid($uuid)->first();

    expect($foundModel)->not->toBeNull()
        ->and($foundModel->uuid)->toBe((string) $uuid);
});
