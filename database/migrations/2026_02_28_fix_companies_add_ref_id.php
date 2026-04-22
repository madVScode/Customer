<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix companies table:
     * 1. Tambah ref_id column jika belum ada
     * 2. Backfill ref_id untuk records yang dah ada
     *
     * Deploy:
     *   cp 2026_02_28_fix_companies_add_ref_id.php database/migrations/
     *   php artisan migrate
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Tambah ref_id jika belum ada
            if (!Schema::hasColumn('companies', 'ref_id')) {
                $table->string('ref_id')->nullable()->unique()->after('logo');
            }
        });

        // Backfill ref_id untuk companies yang dah ada tapi takde ref_id
        $companies = DB::table('companies')->whereNull('ref_id')->orWhere('ref_id', '')->get();
        $year      = date('Y');
        $counter   = DB::table('companies')->count();

        foreach ($companies as $company) {
            DB::table('companies')
                ->where('id', $company->id)
                ->update([
                    'ref_id' => 'AGN-' . $year . '-' . str_pad($counter, 5, '0', STR_PAD_LEFT)
                ]);
            $counter--;
        }
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'ref_id')) {
                $table->dropColumn('ref_id');
            }
        });
    }
};