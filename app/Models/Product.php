<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'category_id',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'weight',
        'dimensions',
        'requires_shipping',
        'seo_title',
        'seo_description',
        'og_image_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'requires_shipping' => 'boolean',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
    ];

    /**
     * Global scope for active products
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Global scope for featured products
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for products with stock
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereHas('variants.inventory', function (Builder $q) {
            $q->where('stock_quantity', '>', 0);
        });
    }

    /**
     * Scope for search with optimized indexes
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'ILIKE', "%{$term}%")
              ->orWhere('sku', 'ILIKE', "%{$term}%")
              ->orWhere('short_description', 'ILIKE', "%{$term}%");
        });
    }

    /**
     * Product category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->select(['id', 'name', 'slug', 'parent_id']);
    }

    /**
     * Product variants (sizes, colors, etc)
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Default variant (first variant)
     */
    public function defaultVariant()
    {
        return $this->variants()->first();
    }

    /**
     * Product media (images, videos)
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Product images only
     */
    public function images(): MorphMany
    {
        return $this->media()->where('mime_type', 'like', 'image/%');
    }

    /**
     * Get price range for this product
     */
    public function getPriceRange(): array
    {
        $prices = $this->variants()->pluck('price')->filter();

        if ($prices->isEmpty()) {
            return ['min' => 0, 'max' => 0];
        }

        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
        ];
    }

    /**
     * Generate URL slug from name
     */
    public function generateSlug(): string
    {
        return \Str::slug($this->name);
    }

    /**
     * Get total available quantity across all variants
     */
    public function getTotalQuantity(): int
    {
        return $this->variants()
            ->join('inventories', 'product_variants.id', '=', 'inventories.variant_id')
            ->sum('inventories.available_quantity');
    }

    /**
     * Auto-generate SEO meta title
     */
    public function getAutoMetaTitle(): string
    {
        if ($this->seo_title) {
            return $this->seo_title;
        }

        return $this->name . ' | ' . $this->category->name . ' | İlisan';
    }

    /**
     * Auto-generate SEO meta description
     */
    public function getAutoMetaDescription(): string
    {
        if ($this->seo_description) {
            return $this->seo_description;
        }

        $variants = $this->variants->take(3);
        $colors = $variants->pluck('attributes.color')->filter()->unique()->implode(', ');
        $sizes = $variants->pluck('attributes.size')->filter()->unique()->implode(', ');

        $description = $this->short_description ?? \Str::limit($this->description, 120);

        $variantInfo = '';
        if ($colors) {
            $variantInfo .= $colors . ' renk seçenekleri';
        }
        if ($sizes) {
            $variantInfo .= ($variantInfo ? ' ve ' : '') . $sizes . ' beden seçenekleri';
        }

        return $description . ($variantInfo ? ' ile ' . $variantInfo : '') . '. İlisan güvencesiyle.';
    }

    /**
     * Get SEO-friendly variant URL
     */
    public function getVariantUrl($variant): string
    {
        $baseSlug = $this->slug;

        if ($variant->attributes) {
            $attributes = collect($variant->attributes)
                ->map(function ($value) {
                    return \Str::slug($value);
                })
                ->implode('-');

            return $baseSlug . '/' . $attributes;
        }

        return $baseSlug;
    }

    /**
     * Get available colors from variants
     */
    public function getAvailableColors(): array
    {
        return $this->variants()
            ->whereNotNull('attributes->color')
            ->pluck('attributes->color')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get available sizes from variants
     */
    public function getAvailableSizes(): array
    {
        return $this->variants()
            ->whereNotNull('attributes->size')
            ->pluck('attributes->size')
            ->unique()
            ->values()
            ->toArray();
    }
}
