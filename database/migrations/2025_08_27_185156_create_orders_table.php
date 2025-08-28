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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number')->unique();
            $table->string('status')->default('pending'); // OrderStatus enum
            $table->string('payment_status')->default('pending'); // PaymentStatus enum

            // Customer Information
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('first_name');
            $table->string('last_name');

            // Pricing
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 3)->default('TRY');

            // Addresses (JSON for flexibility)
            $table->json('billing_address');
            $table->json('shipping_address');

            // Timestamps
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Metadata
            $table->json('metadata')->nullable(); // Additional data, notes, etc.
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
            $table->index('placed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
