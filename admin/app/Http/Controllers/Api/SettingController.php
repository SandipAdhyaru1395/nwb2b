<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\SyncUpdate;
use Illuminate\Support\Facades\DB;
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

        // Build versions map from sync_updates table, tolerating schema differences
        $versionsMap = [];
        try {
            $versionsMap = SyncUpdate::query()->pluck('version', 'entity')->toArray();
        } catch (\Throwable $e) {
            try {
                $versionsMap = DB::table('sync_updates')->pluck('version', 'model')->toArray();
            } catch (\Throwable $e2) {
                $versionsMap = [];
            }
        }

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
            'versions' => [
                'Product' => (int)($versionsMap['Product'] ?? 0),
                'Order' => (int)($versionsMap['Order'] ?? 0),
                'Customer' => (int)($versionsMap['Customer'] ?? 0),
            ],
        ]);
    }

    public function deliveryMethods()
    {
        $methods = \App\Models\DeliveryMethod::where('status', 'active')
            ->orderByRaw('COALESCE(sort_order, 9999), name')
            ->get(['id', 'name', 'time', 'rate', 'status']);
        return response()->json([
            'success' => true,
            'delivery_methods' => $methods,
        ]);
    }
}


