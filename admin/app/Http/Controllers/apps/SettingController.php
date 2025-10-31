<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMethod;
use App\Models\Permission;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\Role;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use App\Models\VatMethod;

class SettingController extends Controller
{
  public function viewGeneralSettings()
  {
    $setting = Setting::all()->pluck('value', 'key');
    return view('content.settings.general', compact('setting'));
  }
  
  public function viewDeliveryMethod()
  {
    // $delivery_methods = DeliveryMethod::all();
    return view('content.settings.delivery_method');
  }

  public function viewVatMethod()
  {
    return view('content.settings.vat_method');
  }

  public function deliveryMethodListAjax()
  {
    $methods = DeliveryMethod::orderBy('id', 'desc')->get(['id', 'name', 'time', 'rate', 'status']);

    $data = [];
    foreach ($methods as $key => $method) {
      $data[$key]['id'] = $method->id;
      $data[$key]['name'] = $method->name;
      $data[$key]['time'] = $method->time;
      $data[$key]['rate'] = $method->rate;
      $data[$key]['status'] = $method->status;
    }

    return response()->json(['data' => $data]);
  }

  public function vatMethodListAjax()
  {
    $methods = VatMethod::orderBy('id', 'desc')->get(['id', 'name', 'type', 'amount', 'status']);

    $data = [];
    foreach ($methods as $key => $method) {
      $data[$key]['id'] = $method->id;
      $data[$key]['name'] = $method->name;
      $data[$key]['type'] = $method->type;
      $data[$key]['amount'] = $method->amount;
      $data[$key]['status'] = $method->status;
    }

    return response()->json(['data' => $data]);
  }

  public function deliveryMethodShow(Request $request)
  {
    $method = DeliveryMethod::select('id', 'name', 'time', 'rate', 'status', 'sort_order')
      ->where('id', $request->id)
      ->first();

    return response()->json($method);
  }

  public function vatMethodShow(Request $request)
  {
    $method = VatMethod::select('id', 'name', 'type', 'amount', 'status')
      ->where('id', $request->id)
      ->first();

    return response()->json($method);
  }

  public function deliveryMethodStore(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'dmName' => 'required|string|max:255',
      'dmTime' => 'required|string|max:255',
      'dmPrice' => 'required|numeric|min:0',
      'dmStatus' => 'required|in:Active,Inactive',
      'dmSortOrder' => 'nullable|integer',
    ],[
      'dmName.required' => 'Delivery Name is required.',
      'dmTime.required' => 'Delivery Time is required.',
      'dmPrice.required' => 'Delivery Rate is required.',
      'dmStatus.required' => 'Status is required.',
      'dmSortOrder.integer' => 'The sort order field must be an integer.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    DeliveryMethod::create([
      'name' => $request->dmName,
      'time' => $request->dmTime,
      'rate' => $request->dmPrice,
      'status' => $request->dmStatus,
      'sort_order' => $request->dmSortOrder,
    ]);

    Toastr::success('Delivery method created successfully!');
    return redirect()->back();
  }

  public function vatMethodStore(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'vatName' => 'required|string|max:255',
      'vatType' => 'required|in:Percentage,Fixed',
      'vatAmount' => 'required|numeric|min:0',
      'vatStatus' => 'required|in:Active,Inactive',
    ],[
      'vatName.required' => 'VAT Name is required.',
      'vatType.required' => 'VAT Type is required.',
      'vatAmount.required' => 'VAT Amount is required.',
      'vatStatus.required' => 'VAT Status is required.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'addVatModal')->withInput();
    }

    VatMethod::create([
      'name' => $request->vatName,
      'type' => $request->vatType,
      'amount' => $request->vatAmount,
      'status' => $request->vatStatus,
    ]);

    Toastr::success('VAT method created successfully!');
    return redirect()->back();
  }

  public function deliveryMethodUpdate(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'required|exists:delivery_methods,id',
      'dmName' => 'required|string|max:255',
      'dmTime' => 'required|string|max:255',
      'dmPrice' => 'required|numeric|min:0',
      'dmStatus' => 'required|in:Active,Inactive',
      'dmSortOrder' => 'nullable|integer',
    ],[
      'dmName.required' => 'Delivery Name is required.',
      'dmTime.required' => 'Delivery Time is required.',
      'dmPrice.required' => 'Delivery Rate is required.',
      'dmStatus.required' => 'Status is required.',
      'dmSortOrder.integer' => 'The sort order field must be an integer.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'editModal')->withInput();
    }

    $method = DeliveryMethod::findOrFail($request->id);
    $method->name = $request->dmName;
    $method->time = $request->dmTime;
    $method->rate = $request->dmPrice;
    $method->status = $request->dmStatus;
    $method->sort_order = $request->dmSortOrder;
    $method->save();

    Toastr::success('Delivery method updated successfully!');
    return redirect()->back();
  }

  public function vatMethodUpdate(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'required|exists:vat_methods,id',
      'vatName' => 'required|string|max:255',
      'vatType' => 'required|in:Percentage,Fixed',
      'vatAmount' => 'required|numeric|min:0',
      'vatStatus' => 'required|in:Active,Inactive',
    ],[
      'vatName.required' => 'VAT Name is required.',
      'vatType.required' => 'VAT Type is required.',
      'vatAmount.required' => 'VAT Amount is required.',
      'vatStatus.required' => 'VAT Status is required.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'editVatModal')->withInput();
    }

    $method = VatMethod::findOrFail($request->id);
    $method->name = $request->vatName;
    $method->type = $request->vatType;
    $method->amount = $request->vatAmount;
    $method->status = $request->vatStatus;
    $method->save();

    Toastr::success('VAT method updated successfully!');
    return redirect()->back();
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

  public function viewBannerSettings()
  {
    $setting = Setting::all()->pluck('value', 'key');
    return view('content.settings.banner', compact('setting'));
  }

  public function updateBannerSettings(Request $request)
  {
    $validated = $request->validate([
      'bannerImage' => 'required|image|mimes:jpg,jpeg,png|max:2048'
    ]);

    if ($request->hasFile('bannerImage')) {
      $file = $request->file('bannerImage');
      $existingBanner = Setting::where('key', 'banner')->first();
      
      // Delete existing banner if it exists
      if($existingBanner && $existingBanner->value){
        Storage::disk('public')->delete($existingBanner->value);
      }

      // Store new banner
      $path = $file->store('settings', 'public');

      // Update or create banner setting
      Setting::updateOrCreate(['key' => 'banner'], ['value' => $path]);
    }

    Toastr::success('Banner updated successfully');
    return redirect()->route('settings.banner');
  }

  public function viewMaintenanceSettings()
  {
    $setting = Setting::all()->pluck('value', 'key');
    return view('content.settings.maintenance', compact('setting'));
  }

  public function updateMaintenanceSettings(Request $request)
  {
    $validated = $request->validate([
      'maintenanceEnabled' => 'nullable|in:on,off',
      'maintenanceSecret' => 'nullable|string|max:64'
    ]);

    $enabled = ($validated['maintenanceEnabled'] ?? 'off') === 'on';
    $secret = $validated['maintenanceSecret'] ?? '';

    // Persist secret and single maintenance flag
    Setting::updateOrCreate(['key' => 'maintenance_secret'], ['value' => $secret]);
    Setting::updateOrCreate(['key' => 'maintenance_mode'], ['value' => $enabled ? '1' : '0']);

    try {
      if ($enabled) {
        // Put application into maintenance mode. If secret provided, set bypass.
        $options = [];
        if (!empty($secret)) {
          $options['--secret'] = $secret;
        }
        Artisan::call('down', $options);
        Toastr::success('Application is now in maintenance mode');
      } else {
        Artisan::call('up');
        Toastr::success('Application is now live');
      }
    } catch (\Throwable $e) {
      Toastr::error('Failed to update maintenance mode: ' . $e->getMessage());
    }

    return redirect()->route('settings.maintenance');
  }
}