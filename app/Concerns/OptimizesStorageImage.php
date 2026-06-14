<?php

namespace App\Concerns;

use App\Support\StorageImageOptimizer;

trait OptimizesStorageImage
{
    protected ?string $previousStorageImage = null;

    protected static function bootOptimizesStorageImage(): void
    {
        static::updating(function (self $model): void {
            if ($model->isDirty('image')) {
                $model->previousStorageImage = $model->getOriginal('image');
            }
        });

        static::created(function (self $model): void {
            if (filled($model->image)) {
                StorageImageOptimizer::optimize($model->image);
            }
        });

        static::updated(function (self $model): void {
            if (! $model->wasChanged('image')) {
                return;
            }

            $previousImage = $model->previousStorageImage;
            $model->previousStorageImage = null;

            if (filled($previousImage) && $previousImage !== $model->image) {
                StorageImageOptimizer::deleteVariants($previousImage);
            }

            if (filled($model->image)) {
                StorageImageOptimizer::optimize($model->image);
            }
        });

        static::deleted(function (self $model): void {
            if (filled($model->image)) {
                StorageImageOptimizer::deleteVariants($model->image);
            }
        });
    }
}
