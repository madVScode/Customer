<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            // Tarikh khusus untuk hari aktiviti
            if (!Schema::hasColumn('itineraries', 'day_date')) {
                $table->date('day_date')->nullable()->after('day_number');
            }
            // Masa mula & tamat aktiviti (jika belum ada)
            if (!Schema::hasColumn('itineraries', 'time_from')) {
                $table->time('time_from')->nullable()->after('day_date');
            }
            if (!Schema::hasColumn('itineraries', 'time_to')) {
                $table->time('time_to')->nullable()->after('time_from');
            }
        });
    }

    public function down(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            $table->dropColumnIfExists(['day_date', 'time_from', 'time_to']);
        });
    }
};