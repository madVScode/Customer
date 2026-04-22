<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();

            // Semua foreign key nullable supaya ON DELETE SET NULL boleh berjaya
            $table->char('customer_id', 36)->nullable();
            $table->char('company_id', 36)->nullable();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();

            $table->decimal('total_amount', 10, 2)->default(0);
            $table->unsignedTinyInteger('pax_adult')->default(0);
            $table->unsignedTinyInteger('pax_child')->default(0);
            $table->unsignedTinyInteger('pax_baby')->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('customer_id')
                  ->references('unique_id')
                  ->on('customers')
                  ->onDelete('set null');

            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('set null');

            $table->foreign('trip_id')
                  ->references('id')
                  ->on('trips')
                  ->onDelete('set null');

            $table->foreign('booking_id')
                  ->references('id')
                  ->on('bookings')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['trip_id']);
            $table->dropForeign(['booking_id']);
        });

        Schema::dropIfExists('receipts');
    }
};