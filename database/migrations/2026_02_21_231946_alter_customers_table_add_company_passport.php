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
    Schema::table('customers', function (Blueprint $table) {
        $table->string('company_id')->nullable()->after('unique_id');
        $table->string('country_of_birth')->nullable()->after('gender');
        $table->string('passport_no')->nullable()->after('country_of_birth');
        $table->string('pp_warganegara')->nullable(); // nationality in english
        $table->date('pp_tarikh_lahir')->nullable();   // date of birth (passport)
        $table->string('pp_gender')->nullable();
        $table->date('pp_date_issued')->nullable();
        $table->date('pp_date_expired')->nullable();
        $table->string('pp_issuing_office')->nullable();

        $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('customers', function (Blueprint $table) {
        $table->dropForeign(['company_id']);
        $table->dropColumn([
            'company_id', 'country_of_birth', 'passport_no',
            'pp_warganegara', 'pp_tarikh_lahir', 'pp_gender',
            'pp_date_issued', 'pp_date_expired', 'pp_issuing_office'
        ]);
    });
}
};
