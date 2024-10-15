<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

// Mock the model with the InteractsWithResourceRoute trait
class ResourceRouteTestModel extends Model
{
    use \CleaniqueCoders\Traitify\Concerns\InteractsWithResourceRoute;
}

// Define routes for testing
beforeEach(function () {
    Route::name('resource-route-test-models.')->group(function () {
        Route::get('resource-route-test-models', fn () => 'Index')->name('index');
        Route::get('resource-route-test-models/{resourceRouteTestModel}', fn () => 'Show')->name('show');
    });
});

it('returns the index route for a model', function () {
    $model = new ResourceRouteTestModel;

    $url = $model->getResourceUrl();

    expect($url)->toBe(route('resource-route-test-models.index'));
});

it('returns the show route for a model', function () {
    $model = new ResourceRouteTestModel;
    $model->id = 1; // Simulate a model with an ID

    $url = $model->getResourceUrl('show');

    expect($url)->toBe(route('resource-route-test-models.show', $model));
});

it('returns the correct base route name', function () {
    $model = new ResourceRouteTestModel;

    $baseRouteName = $model->getUrlRouteBaseName();

    expect($baseRouteName)->toBe('resource-route-test-models');
});
