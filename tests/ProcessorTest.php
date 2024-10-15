<?php

use CleaniqueCoders\Traitify\Contracts\Processor;

// Mock a class that implements the Processor contract
class MockProcessor implements Processor
{
    public bool $processed = false;

    public function process(): self
    {
        $this->processed = true;

        return $this;
    }
}

it('processes the object and returns itself', function () {
    $processor = new MockProcessor;

    // Call the process method
    $result = $processor->process();

    // Ensure the method returns the instance and the process is marked as complete
    expect($result)->toBeInstanceOf(MockProcessor::class)
        ->and($processor->processed)->toBeTrue();
});
