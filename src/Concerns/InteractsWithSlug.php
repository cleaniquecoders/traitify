<?php

namespace CleaniqueCoders\Traitify\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait InteractsWithSlug
{
    use HasGeneratorResolver;

    public static function bootInteractsWithSlug()
    {
        static::creating(function (Model $model) {
            if (Schema::hasColumn($model->getTable(), $model->getSlugColumnName()) &&
                empty($model->{$model->getSlugColumnName()}) &&
                ! empty($model->{$model->getSlugSourceColumnName()})) {
                $generator = $model->resolveGenerator(
                    'slug',
                    'slugGenerator',
                    'slugGeneratorConfig'
                );

                $model->{$model->getSlugColumnName()} = $generator->generate([
                    'model' => $model,
                    'column' => $model->getSlugColumnName(),
                    'source' => $model->{$model->getSlugSourceColumnName()},
                ]);
            }
        });

        static::updating(function (Model $model) {
            if (Schema::hasColumn($model->getTable(), $model->getSlugColumnName()) &&
                $model->isDirty($model->getSlugSourceColumnName()) &&
                empty($model->{$model->getSlugColumnName()}) &&
                ! empty($model->{$model->getSlugSourceColumnName()})) {
                $generator = $model->resolveGenerator(
                    'slug',
                    'slugGenerator',
                    'slugGeneratorConfig'
                );

                $model->{$model->getSlugColumnName()} = $generator->generate([
                    'model' => $model,
                    'column' => $model->getSlugColumnName(),
                    'source' => $model->{$model->getSlugSourceColumnName()},
                ]);
            }
        });
    }

    /**
     * Get Slug Column Name.
     */
    public function getSlugColumnName(): string
    {
        return $this->slug_column ?? 'slug';
    }

    /**
     * Get the source column name for slug generation.
     */
    public function getSlugSourceColumnName(): string
    {
        return $this->slug_source_column ?? 'name';
    }

    /**
     * Scope a query to find by slug.
     */
    public function scopeSlug(Builder $query, $value): Builder
    {
        return $query->where($this->getSlugColumnName(), $value);
    }
}
