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
        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('quantity_kg');
            }
            if (!Schema::hasColumn('cart_items', 'fulfillment')) {
                $table->string('fulfillment')->nullable()->default('pickup')->after('customer_name');
            }
            if (!Schema::hasColumn('cart_items', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('fulfillment');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'fulfillment', 'delivery_address']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_name');
        });
    }
};
