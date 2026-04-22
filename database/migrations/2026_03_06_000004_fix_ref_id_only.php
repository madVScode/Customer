<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya besarkan ref_id — TIDAK sentuh unique_id (PRIMARY KEY)
        Schema::table('customers', function (Blueprint $table) {
            $table->string('ref_id', 20)->nullable()->change();
        });
    }

    public function down(): void {}
};