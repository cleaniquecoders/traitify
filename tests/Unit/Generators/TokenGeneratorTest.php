<?php

use CleaniqueCoders\Traitify\Generators\TokenGenerator;

it('generates token with default configuration', function () {
    $generator = new TokenGenerator;
    $token = $generator->generate();

    expect($token)
        ->toBeString()
        ->and(strlen($token))->toBe(128);
});

it('generates token with custom length', function () {
    $generator = new TokenGenerator(['length' => 32]);
    $token = $generator->generate();

    expect(strlen($token))->toBe(32);
});

it('generates token with prefix and suffix', function () {
    $generator = new TokenGenerator([
        'length' => 16,
        'prefix' => 'TKN_',
        'suffix' => '_END',
    ]);
    $token = $generator->generate();

    expect($token)
        ->toStartWith('TKN_')
        ->toEndWith('_END')
        ->and(strlen($token))->toBe(24); // 16 + 4 + 4
});

it('generates uppercase token when configured', function () {
    $generator = new TokenGenerator([
        'length' => 16,
        'uppercase' => true,
    ]);
    $token = $generator->generate();

    expect($token)->toBe(strtoupper($token));
});

it('generates token with alpha pool', function () {
    $generator = new TokenGenerator([
        'length' => 32,
        'pool' => 'alpha',
    ]);
    $token = $generator->generate();

    expect($token)->toMatch('/^[a-zA-Z]+$/');
});

it('generates token with numeric pool', function () {
    $generator = new TokenGenerator([
        'length' => 32,
        'pool' => 'numeric',
    ]);
    $token = $generator->generate();

    expect($token)->toMatch('/^[0-9]+$/');
});

it('generates token with hex pool', function () {
    $generator = new TokenGenerator([
        'length' => 32,
        'pool' => 'hex',
    ]);
    $token = $generator->generate();

    expect($token)
        ->toMatch('/^[a-f0-9]+$/')
        ->and(strlen($token))->toBe(32);
});

it('generates token with alphanumeric pool', function () {
    $generator = new TokenGenerator([
        'length' => 32,
        'pool' => 'alphanumeric',
    ]);
    $token = $generator->generate();

    expect($token)->toMatch('/^[a-zA-Z0-9]+$/');
});

it('validates token correctly', function () {
    $generator = new TokenGenerator(['length' => 32]);
    $token = $generator->generate();

    expect($generator->validate($token))->toBeTrue();
});

it('validates token with prefix and suffix', function () {
    $generator = new TokenGenerator([
        'length' => 16,
        'prefix' => 'TKN_',
        'suffix' => '_END',
    ]);
    $token = $generator->generate();

    expect($generator->validate($token))->toBeTrue();
});

it('invalidates incorrect token length', function () {
    $generator = new TokenGenerator(['length' => 32]);

    expect($generator->validate('short'))->toBeFalse();
});

it('invalidates non-string values', function () {
    $generator = new TokenGenerator;

    expect($generator->validate(123))->toBeFalse()
        ->and($generator->validate(null))->toBeFalse()
        ->and($generator->validate([]))->toBeFalse();
});

it('can update configuration after instantiation', function () {
    $generator = new TokenGenerator(['length' => 32]);
    $generator->setConfig(['length' => 64]);

    $token = $generator->generate();

    expect(strlen($token))->toBe(64);
});

it('can retrieve configuration', function () {
    $generator = new TokenGenerator(['length' => 32, 'prefix' => 'API_']);
    $config = $generator->getConfig();

    expect($config)
        ->toBeArray()
        ->toHaveKey('length', 32)
        ->toHaveKey('prefix', 'API_');
});
