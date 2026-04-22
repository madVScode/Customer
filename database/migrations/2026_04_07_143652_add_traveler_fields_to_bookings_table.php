<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'pax_adult'))
                $table->unsignedSmallInteger('pax_adult')->default(1)->after('notes');
            if (!Schema::hasColumn('bookings', 'pax_child'))
                $table->unsignedSmallInteger('pax_child')->default(0)->after('pax_adult');
            if (!Schema::hasColumn('bookings', 'pax_baby'))
                $table->unsignedSmallInteger('pax_baby')->default(0)->after('pax_child');
            if (!Schema::hasColumn('bookings', 'traveler_details'))
                $table->json('traveler_details')->nullable()->after('pax_baby');
            if (!Schema::hasColumn('bookings', 'details_completed'))
                $table->boolean('details_completed')->default(false)->after('traveler_details');
            if (!Schema::hasColumn('bookings', 'approved_at'))
                $table->timestamp('approved_at')->nullable()->after('details_completed');
            if (!Schema::hasColumn('bookings', 'rejection_reason'))
                $table->text('rejection_reason')->nullable()->after('approved_at');
            if (!Schema::hasColumn('bookings', 'company_id'))
                $table->unsignedBigInteger('company_id')->nullable()->after('trip_id');
            if (!Schema::hasColumn('bookings', 'source'))
                $table->string('source', 50)->nullable()->after('rejection_reason');
        });
    }

    public function down(): void {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'pax_adult','pax_child','pax_baby',
                'traveler_details','details_completed',
                'approved_at','rejection_reason','company_id','source',
            ]);
        });
    }
};