<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();                         // BATCH-001
            $t->string('name');
            $t->unsignedInteger('bags')->default(0);              // healthy bags
            $t->unsignedInteger('lost_bags')->default(0);
            $t->decimal('yield_kg', 8, 2)->default(0);
            $t->string('status')->default('incubating');          // incubating | fruiting | harvested
            $t->date('harvest_date')->nullable();
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->decimal('price', 10, 2);                          // PHP per kg
            $t->decimal('stock_kg', 8, 2)->default(0);
            $t->string('image_url')->nullable();
            $t->timestamps();
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->decimal('quantity_kg', 8, 2);
            $t->decimal('total', 10, 2);
            $t->string('status')->default('pending');             // pending | completed | cancelled
            $t->timestamps();
        });

        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->decimal('quantity_kg', 8, 2);
            $t->decimal('amount', 10, 2);
            $t->timestamp('sold_at')->useCurrent();
            $t->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->string('details')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['activity_logs', 'sales', 'orders', 'products', 'batches'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
