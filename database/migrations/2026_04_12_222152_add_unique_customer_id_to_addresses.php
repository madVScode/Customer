<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buang duplicate guna derived table (MySQL fix)
        DB::statement('
            DELETE FROM addresses
            WHERE id NOT IN (
                SELECT min_id FROM (
                    SELECT MIN(id) as min_id
                    FROM addresses
                    GROUP BY customer_id
                ) AS temp
            )
        ');

        Schema::table('addresses', function (Blueprint $table) {
            $table->unique('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropUnique(['customer_id']);
        });
    }
};