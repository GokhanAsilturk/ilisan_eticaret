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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('tracking_number')->nullable();
            $table->enum('status', ['pending', 'processing', 'shipped', 'in_transit', 'delivered', 'exception'])->default('pending');
            $table->string('carrier')->nullable(); // MNG, Yurtiçi, PTT, etc.
            $table->string('tracking_url')->nullable();
            $table->json('tracking_data')->nullable(); // carrier API response
            $table->decimal('shipping_cost', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable(); // kg
            $table->json('dimensions')->nullable(); // {"length": 30, "width": 20, "height": 10}
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('tracking_number');
            $table->index('carrier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
