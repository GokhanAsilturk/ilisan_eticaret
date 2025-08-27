<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'filename',
        'original_filename',
        'disk',
        'mime_type',
        'size',
        'alt_text',
        'title',
        'sort_order',
        'is_primary',
        'variant_color', // For color-specific product images
        'meta_data',
    ];

    protected $casts = [
        'size' => 'integer',
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
        'meta_data' => 'array',
    ];

    /**
     * Get the parent mediable model (product, variant, etc.)
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: only images
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope: primary media first
     */
    public function scopePrimaryFirst($query)
    {
        return $query->orderByDesc('is_primary')->orderBy('sort_order');
    }

    /**
     * Scope: by color variant
     */
    public function scopeForColor($query, $color)
    {
        return $query->where('variant_color', $color);
    }

    /**
     * URL'i al (WebP destekli)
     */
    public function getUrl(bool $preferWebP = true): string
    {
        $hasWebP = $this->attributes['has_webp'] ?? false;
        $webpPath = $this->attributes['webp_path'] ?? null;

        // WebP varsa ve tercih ediliyorsa
        if ($preferWebP && $hasWebP && $webpPath) {
            return Storage::disk('products')->url($webpPath);
        }

        return Storage::disk('products')->url($this->path);
    }

    /**
     * Tüm varyantları al
     */
    public function getVariants(): array
    {
        return [
            'original' => $this->getUrl(false),
            'webp' => $this->getUrl(true),
            'thumbnail' => $this->getThumbnailUrl(),
        ];
    }

    /**
     * Thumbnail URL'i
     */
    public function getThumbnailUrl(): string
    {
        // Gelecekte thumbnail logic'i buraya gelecek
        return $this->getUrl();
    }

    /**
     * Get optimized image URL with size
     */
    public function getOptimizedUrl($width = null, $height = null): string
    {
        // For future implementation with image optimization service
        $url = $this->url;

        if ($width || $height) {
            // Add query parameters for image resizing
            $params = [];
            if ($width) {
                $params['w'] = $width;
            }
            if ($height) {
                $params['h'] = $height;
            }

            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Auto-generate alt text for SEO
     */
    public function getAutoAltText(): string
    {
        if ($this->alt_text) {
            return $this->alt_text;
        }

        // Generate alt text based on parent model
        if ($this->mediable_type === Product::class) {
            $product = $this->mediable;
            $altText = $product->name;

            if ($this->variant_color) {
                $altText .= ' ' . $this->variant_color . ' rengi';
            }

            return $altText . ' - ' . $product->category->name;
        }

        return $this->title ?? $this->original_filename;
    }

    /**
     * Get human readable file size
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if media is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if media is a video
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Delete physical file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($media) {
            if (Storage::disk($media->disk)->exists($media->filename)) {
                Storage::disk($media->disk)->delete($media->filename);
            }
        });
    }
}
