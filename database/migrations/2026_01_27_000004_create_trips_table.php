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
    Schema::create('trips', function (Blueprint $table) {
    $table->id();
    $table->char('company_id', 36);
    $table->string('country');
    $table->string('state')->nullable();
    $table->string('location');
    $table->string('national_code', 10);
    $table->date('date_from');
    $table->date('date_to');
    $table->decimal('price_adult', 10, 2)->default(0);
    $table->decimal('price_child', 10, 2)->default(0);
    $table->decimal('price_baby', 10, 2)->default(0);
    $table->decimal('deposit', 10, 2)->default(0);
    $table->string('pic1')->nullable();
    $table->string('pic2')->nullable();
    $table->string('pic3')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
});
}

public function down()
{
    Schema::dropIfExists('trips');
}
};
