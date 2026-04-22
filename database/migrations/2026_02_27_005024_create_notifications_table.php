<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('type');     // contoh: user_logout, user_login, booking_created
            $table->string('title');    // contoh: 'User Status Update'
            $table->text('message');    // contoh: 'Admin System is now offline'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};