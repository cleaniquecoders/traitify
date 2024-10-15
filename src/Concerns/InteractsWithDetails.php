<?php

namespace CleaniqueCoders\Traitify\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait InteractsWithDetails
{
    public function getDetails(): array
    {
        return isset($this->with_details)
            ? $this->with_details
            : [];
    }

    public function scopeWithDetails(Builder $query): Builder
    {
        return $query->with($this->getDetails());
    }
}
