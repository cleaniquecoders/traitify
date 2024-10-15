<?php

namespace CleaniqueCoders\Traitify\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait InteractsWithUuid
{
    public static function bootInteractsWithUuid()
    {
        static::creating(function (Model $model) {
            if (Schema::hasColumn($model->getTable(), $model->getUuidColumnName()) && is_null($model->{$model->getUuidColumnName()})) {
                $model->{$model->getUuidColumnName()} = Str::orderedUuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return $this->getUuidColumnName();
    }

    /**
     * Get UUID Column Name.
     */
    public function getUuidColumnName(): string
    {
        return isset($this->uuid_column) ? $this->uuid_column : 'uuid';
    }

    /**
     * Scope a query based on uuid
     */
    public function scopeUuid(Builder $query, $value): Builder
    {
        return $query->where($this->getUuidColumnName(), $value);
    }
}
