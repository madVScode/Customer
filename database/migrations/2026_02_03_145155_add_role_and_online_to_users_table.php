<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin_record')->after('password');
            $table->boolean('is_online')->default(false)->after('role');
            $table->timestamp('last_seen')->nullable()->after('is_online');
            $table->string('avatar')->nullable()->after('last_seen');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_online', 'last_seen', 'avatar']);
        });
    }
};
