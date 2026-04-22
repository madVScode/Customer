<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->string('booking_no')->unique();
        $table->string('customer_id');
        $table->unsignedBigInteger('trip_id');
        $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
        $table->enum('deposit_status', ['not_paid', 'paid'])->default('not_paid');
        $table->decimal('total_amount', 10, 2)->default(0);
        $table->enum('booking_status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
        $table->text('notes')->nullable();
        $table->timestamps();
        $table->softDeletes();

        $table->foreign('customer_id')->references('unique_id')->on('customers')->onDelete('cascade');
        $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('bookings');
}
};
