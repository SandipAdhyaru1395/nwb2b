<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Customer;
use function asset;

class SettingController extends Controller
{
    public function show()
    {
        $settings = Setting::all()->pluck('value', 'key');

        $logoPath = $settings->get('company_logo');
        $logoUrl = $logoPath ? asset('storage/'.$logoPath) : null;

        $bannerPath = $settings->get('banner');
        $bannerUrl = $bannerPath ? asset('storage/'.$bannerPath) : null;

        return response()->json([
            'success' => true,
            'settings' => [
                'company_title' => $settings->get('company_title'),
                'company_logo_url' => $logoUrl,
                'currency' => $settings->get('currency'),
                'currency_symbol' => $settings->get('currency_symbol') ?? '',
                'banner' => $bannerUrl,
                'maintenance_mode_store' => $settings->get('maintenance_mode_store') === '1',
            ],
        ]);
    }
}


