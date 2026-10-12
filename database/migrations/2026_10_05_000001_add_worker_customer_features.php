<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->text('description')->nullable()->after('name');
        });

        Schema::table('orders', function (Blueprint $t) {
            $t->string('code')->nullable()->unique()->after('id');          // ORD-4866
            $t->boolean('customer_hidden')->default(false)->after('status'); // "clear selection" in order history
        });

        Schema::create('monitoring_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->boolean('watered')->default(true);
            $t->string('result')->default('healthy');                        // healthy | attention
            $t->text('notes')->nullable();
            $t->string('photo_path')->nullable();
            $t->timestamps();
        });

        Schema::create('harvests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->decimal('weight_kg', 8, 2);
            $t->unsignedInteger('bags_cleared')->default(0);
            $t->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->decimal('quantity_kg', 8, 2);
            $t->timestamps();
            $t->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('harvests');
        Schema::dropIfExists('monitoring_logs');
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['code', 'customer_hidden']));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('description'));
    }
};
