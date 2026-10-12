<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // One payment can cover several orders (a cart with more than one product).
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();                       // PAY-123456, also sent to PayMongo
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('method');                                    // gcash | maya | cash
            $t->decimal('delivery_fee', 8, 2)->default(0);
            $t->decimal('amount', 10, 2);                            // products + delivery fee
            $t->string('status')->default('unpaid');                 // unpaid | paid | refund_due | refunded
            $t->string('paymongo_session_id')->nullable()->index();
            $t->text('checkout_url')->nullable();
            $t->string('paymongo_payment_id')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });

        Schema::table('orders', function (Blueprint $t) {
            $t->foreignId('payment_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $t->string('fulfillment')->default('pickup');            // pickup | delivery
            $t->string('fulfillment_status')->default('pending');    // pending | ready_for_pickup | out_for_delivery | picked_up | delivered
            $t->string('delivery_address')->nullable();
            $t->string('contact_phone')->nullable();
        });

        // Orders that were already finished before this feature existed
        DB::table('orders')->where('status', 'completed')->update(['fulfillment_status' => 'picked_up']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->dropConstrainedForeignId('payment_id');
            $t->dropColumn(['fulfillment', 'fulfillment_status', 'delivery_address', 'contact_phone']);
        });
        Schema::dropIfExists('payments');
    }
};
