<?php

use CleaniqueCoders\Traitify\Generators\UuidGenerator;
use Illuminate\Support\Str;

it('generates uuid with default configuration (ordered)', function () {
    $generator = new UuidGenerator;
    $uuid = $generator->generate();

    expect($uuid)
        ->toBeString()
        ->and(Str::isUuid($uuid))->toBeTrue();
});

it('generates v4 uuid', function () {
    $generator = new UuidGenerator(['version' => 'v4']);
    $uuid = $generator->generate();

    expect(Str::isUuid($uuid))->toBeTrue();
});

it('generates uuid with prefix and suffix', function () {
    $generator = new UuidGenerator([
        'prefix' => 'UUID_',
        'suffix' => '_END',
    ]);
    $uuid = $generator->generate();

    expect($uuid)
        ->toStartWith('UUID_')
        ->toEndWith('_END');
});

it('generates uuid in hex format', function () {
    $generator = new UuidGenerator(['format' => 'hex']);
    $uuid = $generator->generate();

    expect($uuid)
        ->toBeString()
        ->toMatch('/^[a-f0-9]{32}$/');
});

it('validates uuid correctly', function () {
    $generator = new UuidGenerator;
    $uuid = $generator->generate();

    expect($generator->validate($uuid))->toBeTrue();
});

it('validates uuid with prefix and suffix', function () {
    $generator = new UuidGenerator([
        'prefix' => 'UUID_',
        'suffix' => '_END',
    ]);
    $uuid = $generator->generate();

    expect($generator->validate($uuid))->toBeTrue();
});

it('invalidates non-uuid strings', function () {
    $generator = new UuidGenerator;

    expect($generator->validate('not-a-uuid'))->toBeFalse()
        ->and($generator->validate('12345678'))->toBeFalse();
});

it('invalidates non-string values', function () {
    $generator = new UuidGenerator;

    expect($generator->validate(123))->toBeFalse()
        ->and($generator->validate(null))->toBeFalse()
        ->and($generator->validate([]))->toBeFalse();
});

it('can update configuration after instantiation', function () {
    $generator = new UuidGenerator(['version' => 'ordered']);
    $generator->setConfig(['format' => 'hex']);

    $uuid = $generator->generate();

    expect($uuid)->toMatch('/^[a-f0-9]{32}$/');
});

it('can retrieve configuration', function () {
    $generator = new UuidGenerator(['version' => 'v4', 'prefix' => 'ID_']);
    $config = $generator->getConfig();

    expect($config)
        ->toBeArray()
        ->toHaveKey('version', 'v4')
        ->toHaveKey('prefix', 'ID_');
});
