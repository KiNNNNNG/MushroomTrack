<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $t) {
            $t->json('scanned_bags')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $t) {
            $t->dropColumn('scanned_bags');
        });
    }
};

