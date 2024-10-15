<?php

use CleaniqueCoders\Traitify\Contracts\Execute;

// Mock a class that implements the Execute contract
class MockExecutor implements Execute
{
    public bool $executed = false;

    public function execute(): self
    {
        $this->executed = true;

        return $this;
    }
}

it('executes the object and returns itself', function () {
    $executor = new MockExecutor;

    // Call the execute method
    $result = $executor->execute();

    // Ensure the method returns the instance and the execution is marked as complete
    expect($result)->toBeInstanceOf(MockExecutor::class)
        ->and($executor->executed)->toBeTrue();
});
