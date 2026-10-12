<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // One row per loss report, so reports can group by reason / date / batch
        Schema::create('bag_losses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('monitoring_log_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedInteger('quantity');
            $t->string('reason');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['batch_id', 'created_at']);
        });

        // Which store product received the harvested kilos (null = not added to the store)
        Schema::table('harvests', function (Blueprint $t) {
            $t->foreignId('product_id')->nullable()->after('batch_id')->constrained()->nullOnDelete();
        });

        // Keep the existing total in step: backfill one "other" loss per batch that already has lost bags
        foreach (DB::table('batches')->where('lost_bags', '>', 0)->get() as $b) {
            DB::table('bag_losses')->insert([
                'batch_id' => $b->id, 'user_id' => null, 'quantity' => $b->lost_bags,
                'reason' => 'other', 'notes' => 'Existing total before loss reporting was added',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $t) {
            $t->dropConstrainedForeignId('product_id');
        });
        Schema::dropIfExists('bag_losses');
    }
};
