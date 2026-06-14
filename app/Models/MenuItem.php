<?php

namespace App\Models;

use App\Concerns\HasPriceVariants;
use App\Concerns\OptimizesStorageImage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property string|null $weight
 * @property string|null $price
 * @property list<array{label: string, price: float|string}>|null $price_variants
 * @property string|null $image
 * @property bool $featured
 * @property string|null $contents
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'category_id',
    'name',
    'description',
    'weight',
    'price',
    'price_variants',
    'image',
    'featured',
    'contents',
    'sort_order',
    'is_active',
])]
class MenuItem extends Model
{
    use HasPriceVariants;
    use OptimizesStorageImage;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_variants' => 'array',
            'featured' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MenuItem $item): void {
            if ($item->hasPriceVariants()) {
                $item->price_variants = $item->normalizedPriceVariants();
            } else {
                $item->price_variants = null;
            }

            if (! $item->hasPriceVariants() && $item->price === null) {
                throw ValidationException::withMessages([
                    'price' => 'Unesite cenu ili dodajte bar jednu veličinu pice.',
                ]);
            }
        });
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
