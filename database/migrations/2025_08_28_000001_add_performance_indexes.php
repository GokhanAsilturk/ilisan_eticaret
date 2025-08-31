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
        // Frequently queried columns need indexes

        // Categories table indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'categories_active_sort_idx');
            $table->index('parent_id', 'categories_parent_idx');
            $table->index(['slug', 'is_active'], 'categories_slug_active_idx');
        });

        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'is_featured'], 'products_active_featured_idx');
            $table->index('category_id', 'products_category_idx');
            $table->index(['sku', 'is_active'], 'products_sku_active_idx');
            $table->index(['slug', 'is_active'], 'products_slug_active_idx');
            $table->index('created_at', 'products_created_at_idx');
        });

        // Product variants table indexes
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['product_id', 'is_active'], 'variants_product_active_idx');
            $table->index(['sku', 'is_active'], 'variants_sku_active_idx');
            $table->index('price', 'variants_price_idx');
        });

        // Inventories table indexes
        Schema::table('inventories', function (Blueprint $table) {
            $table->index('variant_id', 'inventory_variant_idx');
            $table->index(['quantity', 'available_quantity'], 'inventory_quantities_idx');
            $table->index(['available_quantity', 'low_stock_threshold'], 'inventory_stock_levels_idx');
        });

        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'orders_user_status_idx');
            $table->index(['status', 'created_at'], 'orders_status_created_idx');
            $table->index('order_number', 'orders_number_idx');
        });

        // Order items table indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id', 'order_items_order_idx');
            $table->index('variant_id', 'order_items_variant_idx');
        });

        // Carts table indexes
        Schema::table('carts', function (Blueprint $table) {
            $table->index(['user_id', 'session_id'], 'carts_user_session_idx');
            $table->index('expires_at', 'carts_expires_idx');
        });

        // Cart items table indexes
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('cart_id', 'cart_items_cart_idx');
            $table->index('variant_id', 'cart_items_variant_idx');
        });

        // Users table additional indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email', 'is_active'], 'users_email_active_idx');
            $table->index('email_verified_at', 'users_email_verified_idx');
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['order_id', 'status'], 'payments_order_status_idx');
            $table->index('gateway_transaction_id', 'payments_gateway_transaction_idx');
            $table->index(['status', 'created_at'], 'payments_status_created_idx');
        });

        // Addresses table indexes
        Schema::table('addresses', function (Blueprint $table) {
            $table->index(['user_id', 'type'], 'addresses_user_type_idx');
            $table->index(['user_id', 'is_default'], 'addresses_user_default_idx');
        });

        // Media table indexes
        Schema::table('media', function (Blueprint $table) {
            $table->index(['mediable_type', 'mediable_id'], 'media_mediable_idx');
            $table->index('sort_order', 'media_sort_order_idx');
        });

        // Audit logs table indexes
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['auditable_type', 'auditable_id'], 'audit_logs_auditable_idx');
            $table->index(['user_id', 'created_at'], 'audit_logs_user_created_idx');
            $table->index('event', 'audit_logs_event_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all performance indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_active_sort_idx');
            $table->dropIndex('categories_parent_idx');
            $table->dropIndex('categories_slug_active_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_featured_idx');
            $table->dropIndex('products_category_idx');
            $table->dropIndex('products_sku_active_idx');
            $table->dropIndex('products_slug_active_idx');
            $table->dropIndex('products_created_at_idx');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('variants_product_active_idx');
            $table->dropIndex('variants_sku_active_idx');
            $table->dropIndex('variants_price_idx');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex('inventory_variant_location_idx');
            $table->dropIndex('inventory_stock_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_status_idx');
            $table->dropIndex('orders_status_created_idx');
            $table->dropIndex('orders_number_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_idx');
            $table->dropIndex('order_items_variant_idx');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('carts_user_session_idx');
            $table->dropIndex('carts_expires_idx');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('cart_items_cart_idx');
            $table->dropIndex('cart_items_variant_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_active_idx');
            $table->dropIndex('users_email_verified_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_order_status_idx');
            $table->dropIndex('payments_gateway_transaction_idx');
            $table->dropIndex('payments_status_created_idx');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex('addresses_user_type_idx');
            $table->dropIndex('addresses_user_default_idx');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('media_mediable_idx');
            $table->dropIndex('media_sort_order_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_auditable_idx');
            $table->dropIndex('audit_logs_user_created_idx');
            $table->dropIndex('audit_logs_event_idx');
        });
    }
};
