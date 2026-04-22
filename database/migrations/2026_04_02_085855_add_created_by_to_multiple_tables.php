<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('is_protected')->index();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->index();
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};
