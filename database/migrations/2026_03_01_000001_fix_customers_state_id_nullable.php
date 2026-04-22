<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix: customers.state_id NOT NULL — tourist booking gagal
     * sebab state_id tak dipass dari tourist portal
     *
     * Deploy:
     *   cp fix_customers_state_id_nullable.php database/migrations/2026_03_01_000001_fix_customers_state_id_nullable.php
     *   php artisan migrate
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Tukar state_id kepada nullable
            $table->unsignedBigInteger('state_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable(false)->change();
        });
    }
};