<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds per-bag flush tracking to batches.
 * flush_count  — number of harvests completed for this batch so far
 * max_flushes  — after this many flushes the bags should be disposed (default 5)
 * disposed_at  — when the batch was marked disposed (null = still in use)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $t) {
            $t->unsignedTinyInteger('flush_count')->default(0)->after('yield_kg');
            $t->unsignedTinyInteger('max_flushes')->default(5)->after('flush_count');
            $t->timestamp('disposed_at')->nullable()->after('harvest_date');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $t) {
            $t->dropColumn(['flush_count', 'max_flushes', 'disposed_at']);
        });
    }
};

