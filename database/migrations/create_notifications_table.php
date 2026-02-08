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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // User yang menerima notification
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // User yang trigger action (optional)
            $table->foreignId('triggered_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Jenis notification
            $table->string('type'); // customer_created, receipt_created, etc

            // Kandungan
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();

            // Read status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            // 🔥 SOFT DELETE (INI YANG FIX DELETE BUTTON)
            $table->timestamp('deleted_at')->nullable()->index();

            // Timestamps
            $table->timestamps();

            // Index untuk performance
            $table->index(['user_id', 'is_read', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
