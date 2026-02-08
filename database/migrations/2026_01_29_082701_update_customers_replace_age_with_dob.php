<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
{
    Schema::table('customers', function (Blueprint $table) {
        // 1️⃣ tambah dob sebagai nullable
        $table->date('dob')->nullable()->after('ic');
    });

    Schema::table('customers', function (Blueprint $table) {
        // 2️⃣ buang age
        $table->dropColumn('age');
    });
}

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // rollback
            $table->integer('age')->after('phone');
            $table->dropColumn('dob');
        });
    }
};

