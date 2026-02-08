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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('unique_id')->primary(); // UUID as primary key
            $table->string('ic', 12)->unique(); // IC Number (12 digits)
            $table->string('name'); // Full name
            $table->string('email')->unique(); // Email address
            $table->string('phone', 20); // Phone number
            $table->integer('age'); // Age
            $table->enum('gender', ['male', 'female']); // Gender (male/female)
            $table->foreignId('state_id')->constrained('states')->cascadeOnDelete(); // State relationship
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at for soft deletes
            
            // Indexes for better search performance
            $table->index('ic');
            $table->index('email');
            $table->index('name');
            $table->index('state_id');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};