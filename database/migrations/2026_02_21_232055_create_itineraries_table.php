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
    Schema::create('itineraries', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('trip_id');
        $table->integer('day_number');
        $table->string('title');
        $table->text('description')->nullable();
        $table->time('time_from')->nullable();
        $table->time('time_to')->nullable();
        $table->timestamps();

        $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('itineraries');
}
};
