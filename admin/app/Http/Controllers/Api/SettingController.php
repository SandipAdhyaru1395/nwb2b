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

        // For demo, fetch the first customer's balance (or zero if none). In a real app, use the authenticated customer.
        $customer = Customer::query()->first();
        $walletBalance = $customer?->credit_balance ?? 0;

        return response()->json([
            'success' => true,
            'settings' => [
                'company_title' => $settings->get('company_title'),
                'company_logo_url' => $logoUrl,
                'currency' => $settings->get('currency'),
                'currency_symbol' => $settings->get('currency_symbol') ?? '',
                'wallet_balance' => (float) $walletBalance,
            ],
        ]);
    }
}


