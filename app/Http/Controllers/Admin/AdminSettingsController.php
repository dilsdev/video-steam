<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminSettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        $settings = [
            // Earning Settings - using EXACT keys from the codebase
            'earning' => [
                'platform_fee_percent' => Setting::get('platform_fee_percent', 50),
                'default_cpm_rate' => Setting::get('default_cpm_rate', 1500), // IDR per 1000 views
            ],
            // Payout Settings
            'payout' => [
                'min_payout' => Setting::get('min_payout', 100000),
                'payout_fee' => Setting::get('payout_fee', 2500),
            ],
            // Membership Settings
            'membership' => [
                'membership_monthly_price' => Setting::get('membership_monthly_price', 20000),
                'membership_yearly_price' => Setting::get('membership_yearly_price', 199000),
            ],
            // Upload Settings
            'upload' => [
                'max_video_size_mb' => Setting::get('max_video_size_mb', 500),
            ],
            // General Settings
            'general' => [
                'site_name' => Setting::get('site_name', config('app.name')),
            ],
        ];

        // Calculate creator revenue share for display
        $creatorShare = 100 - $settings['earning']['platform_fee_percent'];

        return view('admin.settings.index', compact('settings', 'creatorShare'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            // Earning settings
            'platform_fee_percent' => 'required|integer|min:0|max:100',
            'default_cpm_rate' => 'required|numeric|min:0',
            // Payout settings
            'min_payout' => 'required|numeric|min:0',
            'payout_fee' => 'required|numeric|min:0',
            // Membership settings
            'membership_monthly_price' => 'required|numeric|min:0',
            'membership_yearly_price' => 'required|numeric|min:0',
            // Upload settings
            'max_video_size_mb' => 'required|integer|min:1|max:5000',
            // General settings
            'site_name' => 'required|string|max:255',
        ]);

        // Earning settings
        Setting::set('platform_fee_percent', $request->platform_fee_percent, 'int', 'earning');
        Setting::set('default_cpm_rate', $request->default_cpm_rate, 'float', 'earning');

        // Payout settings
        Setting::set('min_payout', $request->min_payout, 'int', 'payout');
        Setting::set('payout_fee', $request->payout_fee, 'int', 'payout');

        // Membership settings
        Setting::set('membership_monthly_price', $request->membership_monthly_price, 'int', 'membership');
        Setting::set('membership_yearly_price', $request->membership_yearly_price, 'int', 'membership');

        // Upload settings
        Setting::set('max_video_size_mb', $request->max_video_size_mb, 'int', 'upload');

        // General settings
        Setting::set('site_name', $request->site_name, 'string', 'general');

        // Clear all related caches
        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan!');
    }

    /**
     * Clear all settings cache
     */
    private function clearSettingsCache(): void
    {
        $keys = [
            'platform_fee_percent',
            'default_cpm_rate',
            'min_payout',
            'payout_fee',
            'membership_monthly_price',
            'membership_yearly_price',
            'max_video_size_mb',
            'site_name',
        ];

        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }
}
