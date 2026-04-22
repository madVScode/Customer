<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Companies
        Schema::table('companies', function (Blueprint $table) {
            $table->string('ref_id', 20)->nullable()->unique()->after('id');
        });

        // Customers
        Schema::table('customers', function (Blueprint $table) {
            $table->string('ref_id', 20)->nullable()->unique()->after('unique_id');
        });

        // Trips
        Schema::table('trips', function (Blueprint $table) {
            $table->string('ref_id', 20)->nullable()->unique()->after('id');
        });

        // Itineraries
        Schema::table('itineraries', function (Blueprint $table) {
            $table->string('ref_id', 20)->nullable()->unique()->after('id');
        });

        // Generate ref_id untuk data yang sedia ada
        $this->generateExistingIds();
    }

    private function generateExistingIds(): void
    {
        $year = date('Y');

        // Companies
        $companies = DB::table('companies')->orderBy('created_at')->get();
        foreach ($companies as $i => $company) {
            $seq = str_pad($i + 1, 5, '0', STR_PAD_LEFT);
            $createdYear = substr($company->created_at, 0, 4);
            DB::table('companies')->where('id', $company->id)
                ->update(['ref_id' => "AGN-{$createdYear}-{$seq}"]);
        }

        // Customers — guna unique_id bukan id
$customers = DB::table('customers')->orderBy('created_at')->get();
foreach ($customers as $i => $customer) {
    $seq = str_pad($i + 1, 5, '0', STR_PAD_LEFT);
    $createdYear = substr($customer->created_at, 0, 4);
    DB::table('customers')->where('unique_id', $customer->unique_id) // ← guna unique_id
        ->update(['ref_id' => "PAX-{$createdYear}-{$seq}"]);
}

        // Trips
        $trips = DB::table('trips')->orderBy('created_at')->get();
        foreach ($trips as $i => $trip) {
            $seq = str_pad($i + 1, 5, '0', STR_PAD_LEFT);
            $createdYear = substr($trip->created_at, 0, 4);
            DB::table('trips')->where('id', $trip->id)
                ->update(['ref_id' => "PKG-{$createdYear}-{$seq}"]);
        }

        // Itineraries
        $itineraries = DB::table('itineraries')->orderBy('created_at')->get();
        foreach ($itineraries as $i => $itin) {
            $seq = str_pad($i + 1, 5, '0', STR_PAD_LEFT);
            $createdYear = substr($itin->created_at, 0, 4);
            DB::table('itineraries')->where('id', $itin->id)
                ->update(['ref_id' => "ITN-{$createdYear}-{$seq}"]);
        }
    }

    public function down(): void
    {
        Schema::table('companies', fn($t) => $t->dropColumn('ref_id'));
        Schema::table('customers', fn($t) => $t->dropColumn('ref_id'));
        Schema::table('trips', fn($t) => $t->dropColumn('ref_id'));
        Schema::table('itineraries', fn($t) => $t->dropColumn('ref_id'));
    }
};