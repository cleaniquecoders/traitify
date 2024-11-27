<?php

use CleaniqueCoders\Traitify\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

use Illuminate\Support\Facades\Route;

if (! function_exists('routes')) {
    /**
     * List all registered routes for debugging in a structured format.
     *
     * @param  bool  $dump  Whether to dump the routes to the console.
     * @return \Illuminate\Support\Collection A collection of routes with details.
     */
    function routes(bool $dump = false)
    {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'methods' => $route->methods(),
            ];
        });

        if ($dump) {
            dump($routes); // Dumps routes to the console
        }

        return $routes;
    }
}
