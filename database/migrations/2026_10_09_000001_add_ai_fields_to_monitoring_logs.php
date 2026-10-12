<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $t) {
            // What the vision model saw. The final "result" column is still the one the app uses.
            $t->string('ai_status')->nullable();                       // healthy | attention | contaminated | unclear
            $t->string('ai_contamination')->nullable();                // none | green_mold | black_mold | ...
            $t->string('ai_confidence')->nullable();                   // low | medium | high
            $t->string('ai_stage')->nullable();                        // colonizing | pinning | young_fruiting | ...
            $t->unsignedSmallInteger('ai_days_to_harvest')->nullable();
            $t->text('ai_summary')->nullable();
            $t->string('ai_model')->nullable();
            $t->string('ai_error')->nullable();                        // set when the AI could not review the photo
            $t->timestamp('ai_analyzed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $t) {
            $t->dropColumn([
                'ai_status', 'ai_contamination', 'ai_confidence', 'ai_stage',
                'ai_days_to_harvest', 'ai_summary', 'ai_model', 'ai_error', 'ai_analyzed_at',
            ]);
        });
    }
};
