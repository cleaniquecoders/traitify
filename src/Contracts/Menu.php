<?php

namespace CleaniqueCoders\Traitify\Contracts;

use Illuminate\Support\Collection;

interface Menu
{
    public function menus(): Collection;
}
