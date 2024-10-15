<?php

use CleaniqueCoders\Traitify\Concerns\InteractsWithToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Mock the model with the InteractsWithToken trait
class TokenTestModel extends Model
{
    use InteractsWithToken;

    protected $table = 'token_test_models';

    protected $fillable = ['name', 'token'];
}

beforeEach(function () {
    // Create the test table
    Schema::create('token_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('token')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('token_test_models');
});

it('automatically generates a token when creating a model', function () {
    // Create a model instance using the trait
    $model = TokenTestModel::create(['name' => 'Test Model']);

    expect($model->token)->toBeString()
        ->and(strlen($model->token))->toBe(128); // Ensure token is 128 characters long
});

it('does not overwrite an existing token', function () {
    $token = Str::random(128);

    // Create a model with a predefined token
    $model = TokenTestModel::create(['name' => 'Test Model', 'token' => $token]);

    expect($model->token)->toBe($token);
});

it('uses a custom token column if specified', function () {
    Schema::table('token_test_models', function (Blueprint $table) {
        $table->renameColumn('token', 'api_token');
    });

    $model = new class extends TokenTestModel
    {
        protected $fillable = ['name', 'api_token'];

        public $token_column = 'api_token';
    };

    $instance = $model::create(['name' => 'Test Model']);

    expect($instance->api_token)->toBeString()
        ->and(strlen($instance->api_token))->toBe(128); // Ensure token is 128 characters long
});

it('returns the correct token column name', function () {
    $model = new TokenTestModel;

    // Default column
    expect($model->getTokenColumn())->toBe('token');

    // Custom column
    $model->token_column = 'api_token';
    expect($model->getTokenColumn())->toBe('api_token');
});

it('can query models by token', function () {
    $token = Str::random(128);

    // Create a model with a token
    TokenTestModel::create(['name' => 'Test Model', 'token' => $token]);

    // Query by token
    $foundModel = TokenTestModel::token($token)->first();

    expect($foundModel)->not->toBeNull()
        ->and($foundModel->token)->toBe($token);
});
