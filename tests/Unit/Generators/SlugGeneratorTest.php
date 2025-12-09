<?php

use CleaniqueCoders\Traitify\Generators\SlugGenerator;

it('generates slug with default configuration', function () {
    $generator = new SlugGenerator;
    $slug = $generator->generate(['source' => 'Hello World']);

    expect($slug)->toBe('hello-world');
});

it('generates empty slug when source is empty', function () {
    $generator = new SlugGenerator;
    $slug = $generator->generate(['source' => '']);

    expect($slug)->toBe('');
});

it('generates slug with custom separator', function () {
    $generator = new SlugGenerator(['separator' => '_']);
    $slug = $generator->generate(['source' => 'Hello World']);

    expect($slug)->toBe('hello_world');
});

it('generates slug with prefix and suffix', function () {
    $generator = new SlugGenerator([
        'prefix' => 'post-',
        'suffix' => '-2024',
    ]);
    $slug = $generator->generate(['source' => 'My Article']);

    expect($slug)->toBe('post-my-article-2024');
});

it('generates slug with max length', function () {
    $generator = new SlugGenerator(['max_length' => 10]);
    $slug = $generator->generate(['source' => 'This is a very long title']);

    expect(strlen($slug))->toBeLessThanOrEqual(10);
});

it('handles special characters in slug', function () {
    $generator = new SlugGenerator;
    $slug = $generator->generate(['source' => 'Hello @World!']);

    expect($slug)->toBe('hello-at-world');
});

it('validates slug correctly', function () {
    $generator = new SlugGenerator;
    $slug = $generator->generate(['source' => 'Hello World']);

    expect($generator->validate($slug))->toBeTrue();
});

it('validates slug with custom separator', function () {
    $generator = new SlugGenerator(['separator' => '_']);

    expect($generator->validate('hello_world'))->toBeTrue()
        ->and($generator->validate('hello-world'))->toBeFalse(); // Different separator should be invalid
});

it('invalidates non-slug strings', function () {
    $generator = new SlugGenerator;

    expect($generator->validate('hello world'))->toBeFalse() // Space not allowed
        ->and($generator->validate('hello@world'))->toBeFalse(); // @ not allowed
});

it('invalidates non-string values', function () {
    $generator = new SlugGenerator;

    expect($generator->validate(123))->toBeFalse()
        ->and($generator->validate(null))->toBeFalse()
        ->and($generator->validate([]))->toBeFalse();
});

it('can update configuration after instantiation', function () {
    $generator = new SlugGenerator(['separator' => '-']);
    $generator->setConfig(['separator' => '_']);

    $slug = $generator->generate(['source' => 'Hello World']);

    expect($slug)->toBe('hello_world');
});

it('can retrieve configuration', function () {
    $generator = new SlugGenerator(['separator' => '_', 'prefix' => 'slug-']);
    $config = $generator->getConfig();

    expect($config)
        ->toBeArray()
        ->toHaveKey('separator', '_')
        ->toHaveKey('prefix', 'slug-');
});

it('generates slug with lowercase disabled', function () {
    $generator = new SlugGenerator(['lowercase' => false]);
    $slug = $generator->generate(['source' => 'Hello World']);

    expect($slug)->toContain('H')->toContain('W');
});

it('handles unicode characters in slug', function () {
    $generator = new SlugGenerator;
    $slug = $generator->generate(['source' => 'Héllo Wörld']);

    expect($slug)->toBe('hello-world');
});
