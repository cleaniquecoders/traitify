<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

// Mock the model with the InteractsWithResourceRoute trait
class TestModel extends Model
{
    protected $url_route_prefix = '';

    use \CleaniqueCoders\Traitify\Concerns\InteractsWithResourceRoute;
}

class AdminTestModel extends Model
{
    protected $url_route_prefix = 'security';

    use \CleaniqueCoders\Traitify\Concerns\InteractsWithResourceRoute;
}

// Define routes for testing
beforeEach(function () {
    Route::name('test-models.')->group(function () {
        Route::get('test-models', fn () => 'Index')->name('index');
        Route::get('test-models/{TestModel}', fn (TestModel $testModel) => 'Show')->name('show');
    });

    Route::prefix('security')->name('security.')->group(function () {
        Route::get('admin-test-models', fn () => 'Admin Index')->name('admin-test-models.index');
        Route::get('admin-test-models/{AdminTestModel}', fn (AdminTestModel $adminTestModel) => 'Admin Show')->name('admin-test-models.show');
    });
});

it('returns the index route for a model', function () {
    $model = new TestModel;

    $url = $model->getResourceUrl();

    expect($url)->toBe(route('test-models.index'));
});

it('returns the show route for a model', function () {
    $model = new TestModel;
    $model->id = 1; // Simulate a model with an ID

    $url = $model->getResourceUrl('show');

    expect($url)->toBe(route('test-models.show', $model));
});

it('returns the correct base route name', function () {
    $model = new TestModel;

    $baseRouteName = $model->getUrlRouteBaseName();

    expect($baseRouteName)->toBe('test-models');
});

it('supports a prefix for the route name', function () {
    $model = new AdminTestModel;
    $model->id = 1;

    $indexUrl = $model->getResourceUrl();
    $showUrl = $model->getResourceUrl('show');

    expect($indexUrl)->toBe(route('security.admin-test-models.index'));
    expect($showUrl)->toBe(route('security.admin-test-models.show', $model));
});

it('returns the correct base route name with a prefix', function () {
    $model = new AdminTestModel;

    $baseRouteName = $model->getUrlRouteBaseName();

    expect($baseRouteName)->toBe('security.admin-test-models');
});
