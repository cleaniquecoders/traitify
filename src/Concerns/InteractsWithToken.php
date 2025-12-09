<?php

namespace CleaniqueCoders\Traitify\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait InteractsWithToken
{
    use HasGeneratorResolver;

    public static function bootInteractsWithToken()
    {
        static::creating(function (Model $model) {
            if (Schema::hasColumn($model->getTable(), $model->getTokenColumn()) && is_null($model->{$model->getTokenColumn()})) {
                $generator = $model->resolveGenerator(
                    'token',
                    'tokenGenerator',
                    'tokenGeneratorConfig'
                );

                $model->{$model->getTokenColumn()} = $generator->generate([
                    'model' => $model,
                    'column' => $model->getTokenColumn(),
                ]);
            }
        });
    }

    /**
     * Get Token Column Name.
     */
    public function getTokenColumn(): string
    {
        return $this->token_column ?? 'token';
    }

    /**
     * Scope a query based on token field.
     */
    public function scopeToken(Builder $query, $value): Builder
    {
        return $query->where($this->getTokenColumn(), $value);
    }
}
