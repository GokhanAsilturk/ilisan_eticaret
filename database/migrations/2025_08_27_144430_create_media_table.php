<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('disk')->default('public');
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->string('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->string('variant_color')->nullable(); // For color-specific images
            $table->json('meta_data')->nullable(); // Additional metadata
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id'], 'media_mediable_index');
            $table->index('variant_color', 'media_variant_color_index');
            $table->index('sort_order', 'media_sort_order_index');
            $table->index('is_primary', 'media_is_primary_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
