<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\Role;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
  public function viewGeneralSettings()
  {
    $setting = Setting::all()->pluck('value', 'key');
    return view('content.settings.general', compact('setting'));
  }

  public function updateGeneralSettings(Request $request)
  {
    $validated = $request->validate([
      'companyTitle' => 'required|string|max:255',
      'companyLogo' => 'nullable|image|mimes:jpg,jpeg,png',
      'companyName' => 'required|string|max:255',
      'companyAddress' => 'nullable|string|max:500',
      'companyEmail' => 'nullable|email|max:255',
      'companyPhone' => 'nullable|string|max:50',
      'defaultVatRate' => 'nullable|numeric|min:0|max:100',
      'sessionTimeout' => 'nullable|integer|min:1',
      'minOrderAmount' => 'nullable|numeric|min:0',
      'currency' => 'nullable|string|max:10',
      'currencySymbol' => 'nullable|string|max:10'
    ]);

    $map = [
      'company_title' => $validated['companyTitle'],
      'company_name' => $validated['companyName'],
      'company_address' => $validated['companyAddress'] ?? '',
      'company_email' => $validated['companyEmail'] ?? '',
      'company_phone' => $validated['companyPhone'] ?? '',
      'default_vat_rate' => $validated['defaultVatRate'] ?? '',
      'session_timeout' => $validated['sessionTimeout'] ?? '',
      'min_order_amount' => $validated['minOrderAmount'] ?? '',
      'currency' => $validated['currency'] ?? '',
      'currency_symbol' => $validated['currencySymbol'] ?? ''
    ];

    foreach ($map as $key => $value) {
      Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    if ($request->hasFile('companyLogo')) {
      $file = $request->file('companyLogo');
      $image = Setting::where('key', 'company_logo')->first()->value;
      
      if($image){
        Storage::disk('public')->delete($image);
      }

      $path = $file->store('settings', 'public');

      Setting::where('key', 'company_logo')->update(['value' => $path]);
    }

    Toastr::success('General settings updated successfully');
    return redirect()->route('settings.general');
  }
}