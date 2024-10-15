<?php

use CleaniqueCoders\Traitify\Concerns\InteractsWithApi;
use CleaniqueCoders\Traitify\Contracts\Api;
use Illuminate\Http\Request;

// Mock a class that implements the Api interface and uses the InteractsWithApi trait
class ApiTestModel implements Api
{
    use InteractsWithApi;

    public static function toArray(Request $request): array
    {
        return ['key' => 'value'];
    }

    public function getMessage(): string
    {
        return 'Success';
    }

    // Override the getCode method to return a custom code
    public function getCode(): int
    {
        return 201;
    }
}

it('returns a valid API response', function () {
    $model = new ApiTestModel;

    $request = new Request;
    $response = $model->getApiResponse($request);

    expect($response)->toMatchArray([
        'data' => ['key' => 'value'],
        'message' => 'Success',
        'code' => 201,
    ]);
});

it('returns the correct data', function () {
    $model = new ApiTestModel;

    $request = new Request;
    $data = $model->getData($request);

    expect($data)->toBeArray()->toMatchArray(['key' => 'value']);
});

it('returns the correct message', function () {
    $model = new ApiTestModel;

    $message = $model->getMessage();

    expect($message)->toBe('Success');
});

it('returns the correct code', function () {
    $model = new ApiTestModel;

    $code = $model->getCode();

    expect($code)->toBe(201);
});
