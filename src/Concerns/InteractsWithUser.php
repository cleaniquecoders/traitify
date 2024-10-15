<?php

namespace CleaniqueCoders\Traitify\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait InteractsWithUser
{
    public static function bootInteractsWithUser()
    {
        static::creating(function ($model) {
            if (Schema::hasColumn($model->getTable(), $model->getUserIdColumnName()) && is_null($model->user_id) && Auth::user()) {
                $model->{$model->getUserIdColumnName()} = Auth::user()->id;
            }
        });
    }

    /**
     * Get User's ID Column Name.
     */
    public function getUserIdColumnName(): string
    {
        return isset($this->user_id_column) ? $this->user_id_column : 'user_id';
    }
}
