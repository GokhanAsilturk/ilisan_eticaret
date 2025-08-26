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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->unique()->constrained('product_variants')->onDelete('cascade');
            $table->integer('quantity')->default(0); // Total stock
            $table->integer('reserved_quantity')->default(0); // Reserved for pending orders
            $table->integer('available_quantity')->default(0); // Available = quantity - reserved
            $table->integer('low_stock_threshold')->default(10);
            $table->boolean('track_quantity')->default(true);
            $table->timestamps();

            $table->index(['variant_id']);
            $table->index(['available_quantity']);
            $table->index(['track_quantity', 'available_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
