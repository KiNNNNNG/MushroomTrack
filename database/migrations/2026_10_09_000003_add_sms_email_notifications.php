<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('notify_sms')->default(false);    // opt-in: every text costs money and needs consent
            $t->boolean('notify_email')->default(true);
        });

        // A small audit trail so "why did I not get my SMS?" can be answered
        Schema::create('notice_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('notice_id')->nullable()->constrained()->nullOnDelete();
            $t->string('channel');                        // sms | email
            $t->string('recipient');                      // masked
            $t->string('title');
            $t->string('status');                         // sent | failed | skipped
            $t->string('error')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_deliveries');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['notify_sms', 'notify_email']));
    }
};
