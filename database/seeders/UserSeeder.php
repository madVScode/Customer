<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin Manage Data - Full access (CRUD)
        User::create([
            'name' => 'Admin Manage',
            'email' => 'admin@customerpro.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin_manage',
            'email_verified_at' => now(),
        ]);

        // Admin Simpan Rekod - Export/View only
        User::create([
            'name' => 'Admin Record',
            'email' => 'record@customerpro.com',
            'password' => Hash::make('record123'),
            'role' => 'admin_record',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Default users created:');
        $this->command->info('   Admin Manage: admin@customerpro.com / admin123');
        $this->command->info('   Admin Record: record@customerpro.com / record123');
    }
}