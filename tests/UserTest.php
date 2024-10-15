<?php

use CleaniqueCoders\Traitify\Concerns\InteractsWithUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

// Mock the model with the InteractsWithUser trait
class UserTestModel extends Model
{
    use InteractsWithUser;

    protected $table = 'user_test_models';

    protected $fillable = ['name', 'user_id'];
}

beforeEach(function () {
    // Create the test table
    Schema::create('user_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->timestamps();
    });

    // Mock an authenticated user
    $user = new class
    {
        public $id = 1;
    };
    Auth::shouldReceive('user')->andReturn($user);
});

afterEach(function () {
    Schema::dropIfExists('user_test_models');
});

it('automatically assigns the authenticated user id when creating a model', function () {
    // Create a model instance using the trait
    $model = UserTestModel::create(['name' => 'Test Model']);

    expect($model->user_id)->toBe(1);
});

it('does not overwrite the existing user id', function () {
    // Create a model with a predefined user_id
    $model = UserTestModel::create(['name' => 'Test Model', 'user_id' => 999]);

    expect($model->user_id)->toBe(999);
});

it('uses a custom user id column if specified', function () {
    // Rename the user_id column to creator_id
    Schema::table('user_test_models', function (Blueprint $table) {
        $table->renameColumn('user_id', 'creator_id');
    });

    // Create a new model using the trait with custom column name
    $model = new class extends UserTestModel
    {
        public $user_id_column = 'creator_id';

        protected $fillable = ['name', 'creator_id'];
    };

    $instance = $model::create(['name' => 'Test Model']);

    expect($instance->creator_id)->toBe(1);
});

it('returns the correct user id column name', function () {
    $model = new UserTestModel;

    // Default column
    expect($model->getUserIdColumnName())->toBe('user_id');

    // Custom column
    $model->user_id_column = 'creator_id';
    expect($model->getUserIdColumnName())->toBe('creator_id');
});
