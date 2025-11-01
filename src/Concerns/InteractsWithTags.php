<?php

namespace CleaniqueCoders\Traitify\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait InteractsWithTags
{
    /**
     * Boot the trait.
     */
    public static function bootInteractsWithTags()
    {
        static::saving(function (Model $model) {
            if (Schema::hasColumn($model->getTable(), $model->getTagsColumnName())) {
                $tags = $model->{$model->getTagsColumnName()};

                if (is_string($tags)) {
                    $model->{$model->getTagsColumnName()} = $model->normalizeTags($tags);
                }
            }
        });
    }

    /**
     * Get the tags column name.
     */
    public function getTagsColumnName(): string
    {
        return $this->tags_column ?? 'tags';
    }

    /**
     * Get tags as array.
     */
    public function getTags(): array
    {
        $tags = $this->{$this->getTagsColumnName()} ?? [];

        return is_string($tags) ? json_decode($tags, true) ?? [] : (array) $tags;
    }

    /**
     * Set tags.
     *
     * @param  mixed  $tags
     * @return $this
     */
    public function setTags($tags): self
    {
        $this->{$this->getTagsColumnName()} = $this->normalizeTags($tags);

        return $this;
    }

    /**
     * Add one or multiple tags.
     *
     * @param  mixed  $tags
     * @return $this
     */
    public function addTags($tags): self
    {
        $existingTags = $this->getTags();
        $newTags = $this->normalizeTags($tags);

        $mergedTags = array_unique(array_merge($existingTags, $newTags));

        $this->{$this->getTagsColumnName()} = $mergedTags;

        return $this;
    }

    /**
     * Remove one or multiple tags.
     *
     * @param  mixed  $tags
     * @return $this
     */
    public function removeTags($tags): self
    {
        $existingTags = $this->getTags();
        $tagsToRemove = $this->normalizeTags($tags);

        $remainingTags = array_values(array_diff($existingTags, $tagsToRemove));

        $this->{$this->getTagsColumnName()} = $remainingTags;

        return $this;
    }

    /**
     * Clear all tags.
     *
     * @return $this
     */
    public function clearTags(): self
    {
        $this->{$this->getTagsColumnName()} = [];

        return $this;
    }

    /**
     * Check if the model has any of the given tags.
     *
     * @param  mixed  $tags
     */
    public function hasTag($tags): bool
    {
        $existingTags = $this->getTags();
        $tagsToCheck = $this->normalizeTags($tags);

        foreach ($tagsToCheck as $tag) {
            if (in_array($tag, $existingTags)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the model has all of the given tags.
     *
     * @param  mixed  $tags
     */
    public function hasAllTags($tags): bool
    {
        $existingTags = $this->getTags();
        $tagsToCheck = $this->normalizeTags($tags);

        foreach ($tagsToCheck as $tag) {
            if (! in_array($tag, $existingTags)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scope a query to models with any of the given tags.
     */
    public function scopeWithAnyTags(Builder $query, $tags): Builder
    {
        $tags = $this->normalizeTags($tags);
        $column = $this->getTagsColumnName();

        return $query->where(function (Builder $query) use ($tags, $column) {
            foreach ($tags as $tag) {
                $query->orWhereJsonContains($column, $tag);
            }
        });
    }

    /**
     * Scope a query to models with all of the given tags.
     */
    public function scopeWithAllTags(Builder $query, $tags): Builder
    {
        $tags = $this->normalizeTags($tags);
        $column = $this->getTagsColumnName();

        foreach ($tags as $tag) {
            $query->whereJsonContains($column, $tag);
        }

        return $query;
    }

    /**
     * Scope a query to models without any of the given tags.
     */
    public function scopeWithoutTags(Builder $query, $tags): Builder
    {
        $tags = $this->normalizeTags($tags);
        $column = $this->getTagsColumnName();

        foreach ($tags as $tag) {
            $query->whereJsonDoesntContain($column, $tag);
        }

        return $query;
    }

    /**
     * Scope a query to models without all of the given tags.
     */
    public function scopeWithoutAnyTags(Builder $query, $tags): Builder
    {
        $tags = $this->normalizeTags($tags);
        $column = $this->getTagsColumnName();

        return $query->where(function (Builder $query) use ($tags, $column) {
            foreach ($tags as $tag) {
                $query->whereJsonDoesntContain($column, $tag);
            }
        });
    }

    /**
     * Scope a query to models that have tags.
     */
    public function scopeHasTags(Builder $query): Builder
    {
        $column = $this->getTagsColumnName();

        return $query->whereNotNull($column)
            ->where($column, '!=', '[]')
            ->where($column, '!=', '');
    }

    /**
     * Scope a query to models that don't have tags.
     */
    public function scopeHasNoTags(Builder $query): Builder
    {
        $column = $this->getTagsColumnName();

        return $query->where(function (Builder $query) use ($column) {
            $query->whereNull($column)
                ->orWhere($column, '[]')
                ->orWhere($column, '');
        });
    }

    /**
     * Normalize tags input to an array.
     *
     * @param  mixed  $tags
     */
    protected function normalizeTags($tags): array
    {
        if (is_null($tags)) {
            return [];
        }

        if (is_string($tags)) {
            return array_values(array_filter(array_map('trim', explode(',', $tags))));
        }

        if (is_array($tags)) {
            return array_values(array_filter($tags));
        }

        return (array) $tags;
    }

    /**
     * Cast tags attribute to array.
     */
    protected function initializeInteractsWithTags()
    {
        $column = $this->getTagsColumnName();

        if (! isset($this->casts[$column])) {
            $this->casts[$column] = 'array';
        }
    }
}
