<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdConfig;

class AdConfigSeeder extends Seeder
{
    public function run(): void
    {
        AdConfig::create([
            'name' => 'Pre-roll Ad',
            'type' => 'preroll',
            'provider' => 'custom',
            'script' => '<div style="background:linear-gradient(135deg,#1e3a5f,#0d1b2a);padding:3rem;border-radius:12px;text-align:center;"><p style="color:#64748b;margin-bottom:1rem;">Sponsor Message</p><h3 style="color:white;font-size:1.5rem;">Iklan Placeholder</h3><p style="color:#94a3b8;margin-top:1rem;">Hubungi admin untuk beriklan di sini</p></div>',
            'duration' => 5,
            'cpm_rate' => 2.00,
            'priority' => 10,
            'is_active' => true
        ]);
    }
}
