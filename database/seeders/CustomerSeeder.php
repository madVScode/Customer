<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            1=>'Johor',2=>'Kedah',3=>'Kelantan',4=>'Melaka',5=>'Negeri Sembilan',
            6=>'Pahang',7=>'Pulau Pinang',8=>'Perak',9=>'Perlis',10=>'Selangor',
            11=>'Terengganu',12=>'Sabah',13=>'Sarawak',14=>'W.P Kuala Lumpur',
            15=>'W.P Labuan',16=>'W.P Putrajaya'
        ];

        for ($i=1; $i<=50; $i++) {

            // IC logic
            $year = rand(80, 05); // 1980 - 2005
            $month = str_pad(rand(1,12),2,'0',STR_PAD_LEFT);
            $day = str_pad(rand(1,28),2,'0',STR_PAD_LEFT);
            $stateCode = array_rand($states);
            $last = rand(0,9);

            $ic = $year.$month.$day.str_pad($stateCode,2,'0',STR_PAD_LEFT).'00'.$last;

            $gender = ($last % 2 == 0) ? 'Perempuan' : 'Lelaki';
            $age = date('Y') - (1900 + $year);

            DB::table('customers')->insert([
    'unique_id' => (string) Str::ulid(), // ✅ WAJIB
    'ic' => $ic,
    'name' => "Customer $i",
    'email' => "customer$i@example.com",
    'phone' => '01'.rand(0,9).rand(1000000,9999999),
    'age' => $age,
    'gender' => $gender,
    'state' => $states[$stateCode],
    'created_at' => Carbon::now(),
    'updated_at' => Carbon::now(),
]);

        }
    }
}
