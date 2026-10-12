<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('username')->nullable()->unique();
            $t->string('phone')->nullable();
            $t->string('address')->nullable();
            $t->string('avatar')->nullable();
            $t->boolean('notify_in_app')->default(true);
        });

        // Give existing users a username based on their email (eleanor@... -> eleanor)
        $taken = [];
        foreach (DB::table('users')->orderBy('id')->get() as $u) {
            $base = Str::slug(Str::before($u->email, '@'), '_') ?: 'user';
            $name = $base; $i = 1;
            while (in_array($name, $taken, true)) { $name = $base.$i++; }
            $taken[] = $name;
            DB::table('users')->where('id', $u->id)->update(['username' => $name]);
        }

        Schema::create('password_otps', function (Blueprint $t) {
            $t->id();
            $t->string('email')->index();
            $t->string('code');                    // hashed 6-digit OTP
            $t->timestamp('expires_at');
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_otps');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['username', 'phone', 'address', 'avatar', 'notify_in_app']));
    }
};
