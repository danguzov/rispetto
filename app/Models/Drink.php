<?php

namespace App\Models;

use App\Concerns\DisplaysCatalogImage;
use App\Concerns\OptimizesStorageImage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $category_id
 * @property string $name
 * @property string|null $description
 * @property string|null $weight
 * @property string $price
 * @property string|null $image
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
    'image',
    'contents',
    'sort_order',
    'is_active',
])]
class Drink extends Model
{
    use DisplaysCatalogImage;
    use OptimizesStorageImage;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
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

    protected function catalogImageType(): string
    {
        return 'drinks';
    }
}
