<?php

namespace CleaniqueCoders\Traitify\Commands;

use Illuminate\Console\Command;

class TraitifyCommand extends Command
{
    public $signature = 'traitify';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
