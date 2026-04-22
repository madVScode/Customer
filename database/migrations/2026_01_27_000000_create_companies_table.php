<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->string('id')->primary(); // CUID
            $table->unsignedBigInteger('user_id'); // FK ke users.id (bukan unique_id)
            $table->string('name');
            $table->string('registration_no')->unique();
            $table->text('address');
            $table->string('logo')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
};