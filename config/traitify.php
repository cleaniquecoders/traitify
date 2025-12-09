<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Value Generators
    |--------------------------------------------------------------------------
    |
    | Configure default generators for tokens, UUIDs, and slugs.
    | You can specify the generator class and its configuration.
    |
    | Each generator has a 'class' key (the generator class name) and a
    | 'config' key (array of configuration options for that generator).
    |
    */

    'generators' => [
        'token' => [
            'class' => \CleaniqueCoders\Traitify\Generators\TokenGenerator::class,
            'config' => [
                'length' => 128,
                'pool' => 'auto', // 'auto', 'alpha', 'alphanumeric', 'numeric', 'hex'
                'prefix' => '',
                'suffix' => '',
                'uppercase' => false,
            ],
        ],

        'uuid' => [
            'class' => \CleaniqueCoders\Traitify\Generators\UuidGenerator::class,
            'config' => [
                'version' => 'ordered', // 'ordered', 'v1', 'v3', 'v4', 'v5'
                'format' => 'string', // 'string', 'binary', 'hex'
                'prefix' => '',
                'suffix' => '',
                'namespace' => null, // For v3/v5
                'name' => null, // For v3/v5
            ],
        ],

        'slug' => [
            'class' => \CleaniqueCoders\Traitify\Generators\SlugGenerator::class,
            'config' => [
                'separator' => '-',
                'language' => 'en',
                'dictionary' => ['@' => 'at'],
                'lowercase' => true,
                'prefix' => '',
                'suffix' => '',
                'max_length' => null,
                'unique' => false,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Generators
    |--------------------------------------------------------------------------
    |
    | Register custom generators that can be referenced by alias.
    | These can be used in your models by referencing the alias.
    |
    | Example:
    | 'short-token' => [
    |     'class' => \CleaniqueCoders\Traitify\Generators\TokenGenerator::class,
    |     'config' => ['length' => 32],
    | ],
    |
    */

    'custom_generators' => [
        //
    ],
];
