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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id'); // UUID foreign key
            $table->text('address_line'); // Full address
            $table->string('postcode', 10); // Postcode
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraint
            $table->foreign('customer_id')
                  ->references('unique_id')
                  ->on('customers')
                  ->cascadeOnDelete();
            
            // Index for foreign key
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};