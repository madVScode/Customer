<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();
            $table->char('customer_id', 36);
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method');
            $table->text('notes')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint
            $table->foreign('customer_id')
                  ->references('unique_id')
                  ->on('customers')
                  ->onDelete('cascade');

            // Indexes for better performance
            $table->index('customer_id');
            $table->index('issued_at');
            $table->index('receipt_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};