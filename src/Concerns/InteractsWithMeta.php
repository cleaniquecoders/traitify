<?php

namespace CleaniqueCoders\Traitify\Concerns;

use Illuminate\Support\Facades\Schema;

trait InteractsWithMeta
{
    public static function bootInteractsWithMeta()
    {
        static::creating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'meta') && is_null($model->meta)) {
                // Dynamically add the cast to 'meta' as 'array' if not already set
                if (! array_key_exists('meta', $model->getCasts())) {
                    $model->mergeCasts(['meta' => 'array']);
                }

                // Set the default 'meta' value
                $model->meta = $model->defaultMeta();
            }
        });
    }

    public function defaultMeta()
    {
        return property_exists($this, 'default_meta')
            ? $this->default_meta
            : [];
    }
}
