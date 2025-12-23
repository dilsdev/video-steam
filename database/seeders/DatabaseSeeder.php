<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Create Demo Uploader
        User::create([
            'name' => 'Demo Uploader',
            'email' => 'uploader@example.com',
            'password' => Hash::make('password'),
            'role' => 'uploader',
            'balance' => 150000,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Create Demo Viewer
        User::create([
            'name' => 'Demo Viewer',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Create Demo Voucher
        Voucher::create([
            'code' => 'WELCOME50',
            'name' => 'Welcome Discount',
            'description' => 'Diskon 50% untuk member baru',
            'type' => 'percentage',
            'value' => 50,
            'max_uses' => 100,
            'max_uses_per_user' => 1,
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
        ]);

        Voucher::create([
            'code' => 'FLAT10K',
            'name' => 'Flat Discount',
            'description' => 'Potongan Rp 10.000',
            'type' => 'fixed',
            'value' => 10000,
            'max_uses' => 50,
            'max_uses_per_user' => 1,
            'expires_at' => now()->addMonths(1),
            'is_active' => true,
        ]);

        // Run other seeders
        $this->call([
            SettingsSeeder::class,
            AdConfigSeeder::class,
        ]);
    }
}
