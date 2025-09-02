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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 100); // created, updated, deleted, login, etc.
            $table->string('auditable_type'); // App\Models\Product, App\Models\Order, etc.
            $table->unsignedBigInteger('auditable_id')->nullable(); // ID of the audited model
            $table->json('old_values')->nullable(); // previous values
            $table->json('new_values')->nullable(); // new values
            $table->string('url')->nullable(); // request URL
            $table->ipAddress('ip_address')->nullable(); // user IP
            $table->text('user_agent')->nullable(); // browser info
            $table->string('session_id')->nullable(); // session ID
            $table->json('metadata')->nullable(); // extra context data
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'event']);
            $table->index('created_at');
            $table->index('event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
