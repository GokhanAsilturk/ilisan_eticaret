<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Backend API odaklı Media Service
 * Frontend'den bağımsız olarak medya yönetimi sağlar
 */
class MediaService
{
    private string $disk = 'products';

    /**
     * Ürün için resim yükle
     */
    public function uploadProductImage(
        UploadedFile $file,
        Product $product,
        ?string $color = null,
        bool $isPrimary = false,
        ?int $sortOrder = null
    ): Media {
        // Validate file
        $this->validateFile($file);

        // Generate filename
        $filename = $this->generateFilename($file, $product, $color);

        // Directory structure: products/{product-id}/{color}/
        $directory = $this->getProductDirectory($product, $color);

        // Store file
        $path = Storage::disk($this->disk)->putFileAs(
            $directory,
            $file,
            $filename
        );

        // Create media record
        return Media::create([
            'mediable_type' => Product::class,
            'mediable_id' => $product->id,
            'filename' => $filename,
            'path' => $path,
            'disk' => $this->disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt_text' => $this->generateAltText($product, $color),
            'sort_order' => $sortOrder ?? $this->getNextSortOrder($product, $color),
            'attributes' => [
                'color' => $color,
                'is_primary' => $isPrimary,
            ]
        ]);
    }

    /**
     * Media URL'ini al (API response için)
     */
    public function getMediaUrl(Media $media): string
    {
        return Storage::disk($media->disk)->url($media->path);
    }

    /**
     * Media sil
     */
    public function deleteMedia(Media $media): bool
    {
        // Delete file from storage
        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }

        // Delete database record
        return $media->delete();
    }

    /**
     * Ürün medyalarını al
     */
    public function getProductMedia(Product $product, ?string $color = null): Collection
    {
        return $product->media()
            ->when($color, fn ($q) => $q->where('attributes->color', $color))
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * File validation
     */
    private function validateFile(UploadedFile $file): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Invalid file type. Only JPEG, PNG, and WebP are allowed.');
        }

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size exceeds 10MB limit.');
        }
    }

    /**
     * Generate filename
     */
    private function generateFilename(UploadedFile $file, Product $product, ?string $color): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $colorSuffix = $color ? '-' . Str::slug($color) : '';

        return "{$product->slug}-{$timestamp}{$colorSuffix}.{$extension}";
    }

    /**
     * Get product directory
     */
    private function getProductDirectory(Product $product, ?string $color): string
    {
        $directory = "products/{$product->id}";

        if ($color) {
            $directory .= '/' . Str::slug($color);
        }

        return $directory;
    }

    /**
     * Generate ALT text for SEO
     */
    private function generateAltText(Product $product, ?string $color): string
    {
        $altText = $product->name;

        if ($color) {
            $altText .= " - {$color}";
        }

        if ($product->category) {
            $altText .= " | {$product->category->name}";
        }

        return $altText;
    }

    /**
     * Get next sort order
     */
    private function getNextSortOrder(Product $product, ?string $color): int
    {
        return Media::where('mediable_type', Product::class)
            ->where('mediable_id', $product->id)
            ->when($color, fn ($q) => $q->where('attributes->color', $color))
            ->max('sort_order') + 1;
    }
}
