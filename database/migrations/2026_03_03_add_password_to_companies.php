<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Password untuk company login portal
            $table->string('password')->nullable()->after('logo');

            // Aktif/tidak company login
            $table->boolean('portal_active')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['password', 'portal_active']);
        });
    }
};