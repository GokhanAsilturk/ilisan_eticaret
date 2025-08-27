<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');

            // Product snapshot (in case variant gets deleted)
            $table->string('product_name');
            $table->string('variant_name');
            $table->string('product_sku');
            $table->string('variant_sku');

            // Pricing at time of order
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Unit price
            $table->decimal('total', 10, 2); // quantity * price

            // Metadata
            $table->json('variant_attributes')->nullable(); // Color, size, etc.
            $table->decimal('weight', 8, 2)->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'variant_id']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
