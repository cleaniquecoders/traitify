<?php

use CleaniqueCoders\Traitify\Concerns\InteractsWithDetails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Mock the model with the InteractsWithDetails trait
class DetailsTestModel extends Model
{
    use InteractsWithDetails;

    protected $table = 'details_test_models';

    protected $fillable = ['name'];

    public function details(): HasMany
    {
        return $this->hasMany(DetailsRelatedModel::class, 'details_test_model_id');
    }
}

class DetailsRelatedModel extends Model
{
    protected $table = 'details_related_models';

    protected $fillable = ['details_test_model_id', 'description'];
}

// Define table structure
beforeEach(function () {
    Schema::create('details_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('details_related_models', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('details_test_model_id');
        $table->string('description');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('details_test_models');
    Schema::dropIfExists('details_related_models');
});

it('returns an empty array if with_details is not set', function () {
    $model = new DetailsTestModel;

    expect($model->getDetails())->toBeArray()->toBeEmpty();
});

it('returns an array of details when with_details is set', function () {
    $model = new class extends DetailsTestModel
    {
        protected $with_details = ['details'];
    };

    expect($model->getDetails())->toBeArray()->toMatchArray(['details']);
});

it('applies eager loading using scopeWithDetails', function () {
    // Create a model with related details
    $parent = DetailsTestModel::create(['name' => 'Test Model']);
    $parent->details()->createMany([
        ['description' => 'Detail 1'],
        ['description' => 'Detail 2'],
    ]);

    // Retrieve the model with details using scopeWithDetails
    $model = new class extends DetailsTestModel
    {
        protected $with_details = ['details'];
    };

    $results = $model::withDetails()->first();

    expect($results->relationLoaded('details'))->toBeTrue()
        ->and($results->details)->toHaveCount(2);
});
