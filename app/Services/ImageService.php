<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    private ImageManager $imageManager;
    private string $disk = 'products';

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Ürün için resim yükle ve işle
     */
    public function uploadProductImage(
        UploadedFile $file,
        Product $product,
        ?string $color = null,
        bool $isPrimary = false,
        ?int $sortOrder = null
    ): Media {
        // Dosya adı oluştur
        $filename = $this->generateFilename($file, $product, $color);

        // Dizin yapısı: products/{product-id}/{color}/
        $directory = $this->getProductDirectory($product, $color);

        // Orijinal dosyayı kaydet
        $originalPath = Storage::disk($this->disk)->putFileAs(
            $directory,
            $file,
            $filename
        );

        // WebP varyantını oluştur (kuyrukta)
        dispatch(function () use ($originalPath, $directory, $filename) {
            $this->createWebPVariant($originalPath, $directory, $filename);
        });

        // Veritabanına kaydet
        return Media::create([
            'mediable_type' => Product::class,
            'mediable_id' => $product->id,
            'filename' => $filename,
            'path' => $originalPath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt_text' => $this->generateAltText($product, $color),
            'attributes' => [
                'color' => $color,
                'is_primary' => $isPrimary,
                'has_webp' => false, // Kuyrukta oluşturulunca true olacak
            ],
            'sort_order' => $sortOrder ?? $this->getNextSortOrder($product, $color),
        ]);
    }

    /**
     * WebP varyantı oluştur
     */
    private function createWebPVariant(string $originalPath, string $directory, string $filename): void
    {
        try {
            $originalFullPath = Storage::disk($this->disk)->path($originalPath);

            // WebP filename
            $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
            $webpPath = $directory . '/' . $webpFilename;

            // Image processing
            $image = $this->imageManager->read($originalFullPath);

            // Optimize edilmiş boyutlar
            $image = $image->scaleDown(width: 1200, height: 1200);

            // WebP olarak kaydet
            $webpFullPath = Storage::disk($this->disk)->path($webpPath);
            $image->toWebp(quality: 85)->save($webpFullPath);

            // Media kaydını güncelle
            Media::where('path', $originalPath)->update([
                'attributes->has_webp' => true,
                'attributes->webp_path' => $webpPath,
            ]);

        } catch (\Exception $e) {
            \Log::error('WebP creation failed', [
                'original_path' => $originalPath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ürün varyantı için resim yükle
     */
    public function uploadVariantImage(
        UploadedFile $file,
        ProductVariant $variant,
        bool $isPrimary = false
    ): Media {
        $color = $variant->attributes['color'] ?? null;

        return $this->uploadProductImage(
            $file,
            $variant->product,
            $color,
            $isPrimary
        );
    }

    /**
     * Dosya adı oluştur
     */
    private function generateFilename(UploadedFile $file, Product $product, ?string $color): string
    {
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);
        $extension = $file->getClientOriginalExtension();

        $colorPart = $color ? "-{$color}" : '';

        return "product-{$product->id}{$colorPart}-{$timestamp}-{$random}.{$extension}";
    }

    /**
     * Ürün dizini oluştur
     */
    private function getProductDirectory(Product $product, ?string $color): string
    {
        $baseDir = "products/{$product->id}";

        if ($color) {
            $colorSlug = Str::slug($color);
            return "{$baseDir}/{$colorSlug}";
        }

        return "{$baseDir}/general";
    }

    /**
     * Alt text oluştur
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
     * Sonraki sıra numarasını al
     */
    private function getNextSortOrder(Product $product, ?string $color): int
    {
        return Media::where('mediable_type', Product::class)
            ->where('mediable_id', $product->id)
            ->when($color, fn($q) => $q->where('attributes->color', $color))
            ->max('sort_order') + 1;
    }

    /**
     * Resim URL'ini al (WebP destekli)
     */
    public function getImageUrl(Media $media, bool $preferWebP = true): string
    {
        $hasWebP = $media->attributes['has_webp'] ?? false;
        $webpPath = $media->attributes['webp_path'] ?? null;

        // WebP varsa ve tercih ediliyorsa
        if ($preferWebP && $hasWebP && $webpPath) {
            return Storage::disk($this->disk)->url($webpPath);
        }

        // Orijinal dosyayı döndür
        return Storage::disk($this->disk)->url($media->path);
    }

    /**
     * Resim varyantlarını al (responsive için)
     */
    public function getImageVariants(Media $media): array
    {
        return [
            'original' => $this->getImageUrl($media, false),
            'webp' => $this->getImageUrl($media, true),
            'thumbnail' => $this->getThumbnailUrl($media),
        ];
    }

    /**
     * Thumbnail URL'ini al
     */
    private function getThumbnailUrl(Media $media): string
    {
        // Thumbnail oluşturma logic'i buraya gelecek
        return $this->getImageUrl($media);
    }
}
