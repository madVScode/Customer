<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class RefIdGenerator
{
    public static function generate(string $prefix, string $table, string $column = 'ref_id'): string
    {
        $year = date('Y');

        // Ambil sequence terbesar untuk tahun ini
        $last = DB::table($table)
            ->where($column, 'like', "{$prefix}-{$year}-%")
            ->orderByRaw("CAST(SUBSTRING_INDEX({$column}, '-', -1) AS UNSIGNED) DESC")
            ->value($column);

        if ($last) {
            $lastSeq = (int) substr($last, strrpos($last, '-') + 1);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        $seq = str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$seq}";
    }

    public static function company(): string
    {
        $prefix = 'AGN';
        $year   = date('Y');

        $last = DB::table('companies')
            ->where('ref_id', 'like', "{$prefix}-{$year}-%")
            ->orderByRaw("CAST(SUBSTRING_INDEX(ref_id, '-', -1) AS UNSIGNED) DESC")
            ->value('ref_id');

        $nextSeq = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
        return "{$prefix}-{$year}-" . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }

    public static function customer(): string
    {
        $prefix = 'PAX';
        $year   = date('Y');

        // Customer guna unique_id sebagai primary key
        // Query ref_id column terus tanpa bergantung pada primary key
        $last = DB::table('customers')
            ->where('ref_id', 'like', "{$prefix}-{$year}-%")
            ->orderByRaw("CAST(SUBSTRING_INDEX(ref_id, '-', -1) AS UNSIGNED) DESC")
            ->value('ref_id');

        $nextSeq = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
        return "{$prefix}-{$year}-" . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }

    public static function trip(): string
    {
        $prefix = 'PKG';
        $year   = date('Y');

        $last = DB::table('trips')
            ->where('ref_id', 'like', "{$prefix}-{$year}-%")
            ->orderByRaw("CAST(SUBSTRING_INDEX(ref_id, '-', -1) AS UNSIGNED) DESC")
            ->value('ref_id');

        $nextSeq = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
        return "{$prefix}-{$year}-" . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }

    public static function itinerary(): string
    {
        $prefix = 'ITN';
        $year   = date('Y');

        $last = DB::table('itineraries')
            ->where('ref_id', 'like', "{$prefix}-{$year}-%")
            ->orderByRaw("CAST(SUBSTRING_INDEX(ref_id, '-', -1) AS UNSIGNED) DESC")
            ->value('ref_id');

        $nextSeq = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
        return "{$prefix}-{$year}-" . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }
}