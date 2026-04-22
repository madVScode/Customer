<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TesterSeeder extends Seeder
{
    public function run(): void
    {
        echo "🧪 Seeding tester protected data...\n";

        // ✅ GUNA UUID STANDARD (36 CHAR)
        $companyId = '11111111-2222-3333-4444-555555555555';

        // ── Company ───────────────────────────────────────────
        if (!DB::table('companies')->where('id', $companyId)->exists()) {
            DB::table('companies')->insert([
                'id'              => $companyId,
                'is_protected'    => true,
                'ref_id'          => 'DEMO-CO-001',
                'user_id'         => 1,
                'name'            => '🧪 Demo Travel Sdn Bhd',
                'registration_no' => 'DEMO-123456',
                'address'         => 'No. 1, Jalan Demo, 50000 Kuala Lumpur',
                'portal_active'   => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            echo "  ✅ Demo company created\n";
        }

        // ── Customers (UUID FIX) ──────────────────────────────
        $customers = [
            [
                'unique_id' => 'aaaaaaaa-1111-1111-1111-111111111111',
                'ref_id'    => 'PAX-DEMO-001',
                'name'      => 'Ahmad Demo bin Abdullah',
                'email'     => 'ahmad.demo@tester.local',
                'gender'    => 'male',
                'dob'       => '1990-01-01',
                'ic'        => '900101011234',
                'phone'     => '0123456789',
                'passport_no' => 'A1234567',
            ],
            [
                'unique_id' => 'bbbbbbbb-2222-2222-2222-222222222222',
                'ref_id'    => 'PAX-DEMO-002',
                'name'      => 'Siti Demo binti Hassan',
                'email'     => 'siti.demo@tester.local',
                'gender'    => 'female',
                'dob'       => '1985-02-02',
                'ic'        => '850202025678',
                'phone'     => '0134567890',
                'passport_no' => 'B7654321',
            ],
            [
                'unique_id' => 'cccccccc-3333-3333-3333-333333333333',
                'ref_id'    => 'PAX-DEMO-003',
                'name'      => 'Raj Demo s/o Kumar',
                'email'     => 'raj.demo@tester.local',
                'gender'    => 'male',
                'dob'       => '1995-03-03',
                'ic'        => '950303039012',
                'phone'     => '0167890123',
                'passport_no' => 'C9876543',
            ],
        ];

        foreach ($customers as $cust) {
            if (!DB::table('customers')->where('unique_id', $cust['unique_id'])->exists()) {
                DB::table('customers')->insert(array_merge($cust, [
                    'company_id'  => $companyId,
                    'is_protected'=> true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]));
            }
        }
        echo "  ✅ 3 demo customers created\n";

        // ── Trips ─────────────────────────────────────────────
        $trips = [
            ['id' => 9991, 'location' => 'Jepun — Tokyo Demo', 'country' => 'Japan', 'national_code' => 'JPN', 'days' => 30],
            ['id' => 9992, 'location' => 'Turkey — Istanbul Demo', 'country' => 'Turkey', 'national_code' => 'TUR', 'days' => 60],
            ['id' => 9993, 'location' => 'Mekah — Umrah Demo', 'country' => 'Saudi Arabia', 'national_code' => 'SAU', 'days' => 90],
        ];

        foreach ($trips as $trip) {
            if (!DB::table('trips')->where('id', $trip['id'])->exists()) {
                DB::table('trips')->insert([
                    'id'            => $trip['id'],
                    'company_id'    => $companyId,
                    'location'      => $trip['location'],
                    'country'       => $trip['country'],
                    'state'         => null,
                    'national_code' => $trip['national_code'],
                    'date_from'     => Carbon::now()->addDays($trip['days']),
                    'date_to'       => Carbon::now()->addDays($trip['days'] + 7),
                    'price_adult'   => 5000,
                    'price_child'   => 3500,
                    'price_baby'    => 500,
                    'deposit'       => 1000,
                    'is_protected'  => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
        echo "  ✅ 3 demo trips created\n";

        // ── Bookings ──────────────────────────────────────────
        $bookings = [
            [9991, 'aaaaaaaa-1111-1111-1111-111111111111'],
            [9992, 'bbbbbbbb-2222-2222-2222-222222222222'],
            [9993, 'cccccccc-3333-3333-3333-333333333333'],
        ];

        foreach ($bookings as [$id, $custId]) {
            if (!DB::table('bookings')->where('id', $id)->exists()) {
                DB::table('bookings')->insert([
                    'id'             => $id,
                    'customer_id'    => $custId,
                    'trip_id'        => $id,
                    'booking_no'     => 'BKG-DEMO-' . $id,
                    'total_amount'   => 5000,
                    'payment_status' => 'paid',
                    'deposit_status' => 'paid',
                    'booking_status' => 'confirmed',
                    'is_protected'   => true,
                    'notes'          => '🧪 Data demo',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
        echo "  ✅ 3 demo bookings created\n";

        // ── Receipts ──────────────────────────────────────────
        foreach ($bookings as [$id, $custId]) {
            if (!DB::table('receipts')->where('id', $id)->exists()) {
                DB::table('receipts')->insert([
                    'id'              => $id,
                    'receipt_no'      => 'RCP-DEMO-' . $id,
                    'customer_id'     => $custId,
                    'company_id'      => $companyId,
                    'trip_id'         => $id,
                    'total_amount'    => 5000,
                    'payment_method'  => 'online_banking',
                    'issued_at'       => now(),
                    'is_protected'    => true,
                    'deposit_amount'  => 0,
                    'pax_adult'       => 1,
                    'pax_child'       => 0,
                    'pax_baby'        => 0,
                    'notes'           => '🧪 Data demo',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
        echo "  ✅ 3 demo receipts created\n";

        echo "\n✅ Tester seed selesai!\n";
    }
}