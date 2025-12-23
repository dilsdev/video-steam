<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'min_payout', 'value' => '100000', 'type' => 'int', 'group' => 'payout'],
            ['key' => 'payout_fee', 'value' => '2500', 'type' => 'int', 'group' => 'payout'],
            ['key' => 'default_cpm_rate', 'value' => '2.00', 'type' => 'float', 'group' => 'earning'],
            ['key' => 'membership_monthly_price', 'value' => '50000', 'type' => 'int', 'group' => 'membership'],
            ['key' => 'membership_yearly_price', 'value' => '500000', 'type' => 'int', 'group' => 'membership'],
            ['key' => 'max_video_size_mb', 'value' => '500', 'type' => 'int', 'group' => 'upload'],
            ['key' => 'site_name', 'value' => 'VideoStream', 'type' => 'string', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
