<?php

use CleaniqueCoders\Traitify\Contracts\Builder;

// Mock a class that implements the Builder contract
class MockBuilder implements Builder
{
    public bool $isBuilt = false;

    public function build(): self
    {
        $this->isBuilt = true;

        return $this;
    }
}

it('builds the object and returns itself', function () {
    $builder = new MockBuilder;

    // Call the build method
    $result = $builder->build();

    // Ensure the method returns the instance and the build process is complete
    expect($result)->toBeInstanceOf(MockBuilder::class)
        ->and($builder->isBuilt)->toBeTrue();
});
