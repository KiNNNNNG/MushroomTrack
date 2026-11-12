<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->uuid('submission_id')->nullable()->unique();
            $table->unsignedInteger('flush_number')->nullable();
            $table->unique(['batch_id', 'flush_number']);
        });
        // SQLite rebuilds this parent table for a default change, which can cascade-delete history.
        // The model supplies the new default there; MySQL can change it in place.
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('batches', function (Blueprint $table) {
                $table->unsignedTinyInteger('max_flushes')->default(4)->change();
            });
        }
        DB::table('batches')->orderBy('id')->chunkById(100, function ($batches) {
            foreach ($batches as $batch) {
                $harvests = DB::table('harvests')->where('batch_id', $batch->id)->orderBy('created_at')->orderBy('id')->get();
                foreach ($harvests as $index => $harvest) {
                    DB::table('harvests')->where('id', $harvest->id)->update(['flush_number' => $index + 1]);
                }
                $count = max((int) $batch->flush_count, $harvests->count());
                $updates = ['flush_count' => $count, 'max_flushes' => 4];
                if ($count >= 4) {
                    $updates += ['status' => 'disposed', 'disposed_at' => $batch->disposed_at ?? now(), 'bags' => 0, 'harvest_date' => null];
                } elseif ($batch->status === 'harvested' && !$batch->disposed_at && $batch->bags > 0 && $harvests->isNotEmpty()) {
                    // The old endpoint closed a batch after its first harvest.
                    $updates += ['status' => 'fruiting', 'harvest_date' => \Illuminate\Support\Carbon::parse($harvests->last()->created_at)->addDays(14)->toDateString()];
                }
                DB::table('batches')->where('id', $batch->id)->update($updates);
            }
        });
    }

    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropUnique(['batch_id', 'flush_number']);
            $table->dropUnique(['submission_id']);
            $table->dropColumn(['submission_id', 'flush_number']);
        });
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('batches', function (Blueprint $table) {
                $table->unsignedTinyInteger('max_flushes')->default(5)->change();
            });
        }
        // Preserve recorded harvest counts and disposal history.
    }
};
