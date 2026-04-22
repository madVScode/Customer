<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =========================
        // CUSTOMERS
        // =========================
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'is_protected')) {
                $table->boolean('is_protected')->default(false);
            }
        });

        // =========================
        // TRIPS
        // =========================
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'is_protected')) {
                $table->boolean('is_protected')->default(false);
            }
        });

        // =========================
        // BOOKINGS
        // =========================
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'is_protected')) {
                $table->boolean('is_protected')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'is_protected')) {
                $table->dropColumn('is_protected');
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'is_protected')) {
                $table->dropColumn('is_protected');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'is_protected')) {
                $table->dropColumn('is_protected');
            }
        });
    }
};