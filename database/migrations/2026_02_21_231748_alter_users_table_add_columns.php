<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Semak dulu sebelum tambah — elak duplicate column error
            if (!Schema::hasColumn('users', 'unique_id')) {
                $table->string('unique_id')->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'facebook_id')) {
                $table->string('facebook_id')->nullable()->after('google_id');
            }
            // email_verified_at dah ada — skip
        });

        // Generate unique_id untuk user yang belum ada
        DB::table('users')->whereNull('unique_id')->get()->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'unique_id' => \Illuminate\Support\Str::uuid()
            ]);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'unique_id')) {
                $table->dropColumn('unique_id');
            }
            if (Schema::hasColumn('users', 'google_id')) {
                $table->dropColumn('google_id');
            }
            if (Schema::hasColumn('users', 'facebook_id')) {
                $table->dropColumn('facebook_id');
            }
        });
    }
};