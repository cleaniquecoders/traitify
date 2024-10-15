<?php

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;

enum SampleEnum: string
{
    use InteractsWithEnum;

    case FIRST = 'first';
    case SECOND = 'second';
    case THIRD = 'third';

    public function label(): string
    {
        return match ($this) {
            self::FIRST => 'First Label',
            self::SECOND => 'Second Label',
            self::THIRD => 'Third Label',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::FIRST => 'First Description',
            self::SECOND => 'Second Description',
            self::THIRD => 'Third Description',
        };
    }
}

it('returns an array of enum values', function () {
    $values = SampleEnum::values();

    expect($values)->toBeArray()->toMatchArray(['first', 'second', 'third']);
});

it('returns an array of enum labels', function () {
    $labels = SampleEnum::labels();

    expect($labels)->toBeArray()->toMatchArray(['First Label', 'Second Label', 'Third Label']);
});

it('returns an array of options with value, label, and description', function () {
    $options = SampleEnum::options();

    expect($options)->toBeArray()->toHaveCount(3)
        ->and($options[0])->toMatchArray([
            'value' => 'first',
            'label' => 'First Label',
            'description' => 'First Description',
        ])
        ->and($options[1])->toMatchArray([
            'value' => 'second',
            'label' => 'Second Label',
            'description' => 'Second Description',
        ])
        ->and($options[2])->toMatchArray([
            'value' => 'third',
            'label' => 'Third Label',
            'description' => 'Third Description',
        ]);
});
