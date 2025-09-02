<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('gateway')->default('iyzico'); // iyzico, paypal, stripe etc.
            $table->string('gateway_transaction_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'captured', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->json('gateway_response')->nullable();
            $table->json('metadata')->nullable(); // conversation_id, threeds_html vs.
            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'status']);
            $table->index(['gateway', 'gateway_transaction_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
