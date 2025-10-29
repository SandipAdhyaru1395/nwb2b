<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Address;
use App\Helpers\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CustomerController extends Controller
{
  public function index()
  {
    $sales_persons = Helpers::getSalesPersons();

    return view('content.customer.list',compact('sales_persons'));
  }

  private function init($id = null)
  {

    if (!$id) {
      return redirect()->route('customer.list');
    }

    $customer = Customer::query()->findOrFail($id);

    $orderAgg = Order::query()
      ->where('customer_id', $id)
      ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total_amount),0) as total_spent')
      ->first();

    $settings = Helpers::setting();
    $currencySymbol = $settings['currency_symbol'] ?? '$';
    $sales_persons = Helpers::getSalesPersons();

    return [
      'customer' => $customer,
      'ordersCount' => (int) ($orderAgg->orders_count ?? 0),
      'totalSpent' => (float) ($orderAgg->total_spent ?? 0),
      'currencySymbol' => $currencySymbol,
      'sales_persons' => $sales_persons
    ];
  }
  public function overview($id = null)
  {
    $data = $this->init($id);
    return view('content.customer.overview', $data);
  }

  public function ajaxList(Request $request)
  {
    // Build aggregated customer stats
    $customerStats = Customer::query()
      ->leftJoin('orders', 'orders.customer_id', '=', 'customers.id')
      ->groupBy('customers.id', 'customers.email', 'customers.phone', 'customers.credit_balance')
      ->selectRaw('customers.id,customers.email, customers.phone, customers.credit_balance, COUNT(orders.id) as orders_count, COALESCE(SUM(orders.total_amount), 0) as total_spent')
      ->get();

    $data = $customerStats->map(function ($row) {
      return [
        'id' => (int) $row->id,
        // 'customer' => $row->name ?? '—',
        'email' => $row->email ?? '',
        'image' => null, // No avatar stored; handled on client with initials
        'phone' => $row->phone ?? '',
        'credit_balance' => number_format((float) $row->credit_balance, 2, '.', ''),
        'order' => (int) $row->orders_count,
        'total_spent' => number_format((float) $row->total_spent, 2, '.', ''),
      ];
    });

    return response()->json(['data' => $data]);
  }

  public function ordersAjax(Request $request, $id)
  {
    $customer = Customer::query()->findOrFail($id);

    $orders = Order::query()
      ->where('customer_id', $customer->id)
      ->latest('created_at')
      ->get();

    $data = $orders->map(function (Order $order) {
      return [
        'id' => $order->id,
        'order' => $order->order_number ?? $order->id,
        'order_number' => $order->order_number ?? '',
        'date' => optional($order->created_at)->toISOString(),
        'payment_status' => $order->payment_status ?? 'Unpaid',
        'order_status' => $order->status ?? 'New',
        'spent' => number_format((float) ($order->total_amount ?? 0), 2),
      ];
    });

    return response()->json(['data' => $data]);
  }

  public function security($id = null)
  {

    $data = $this->init($id);

    return view('content.customer.security', $data);
  }

  public function branches($id = null)
  {

    $data = $this->init($id);

    // Load addresses for the customer
    $branches = Branch::where('customer_id', $id)
      ->orderBy('created_at', 'asc')
      ->get();

    $data['branches'] = $branches;

    return view('content.customer.branches', $data);
  }


  public function notifications($id = null)
  {

    $data = $this->init($id);

    return view('content.customer.notifications', $data);
  }

  public function destroy($id)
  {
    $customer = Customer::query()->findOrFail($id);
    $customer->delete();

    return response()->json([
      'success' => true,
      'message' => 'Customer deleted successfully.'
    ]);
  }

  public function updatePassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'required|integer|exists:customers,id',
      'newPassword' => 'required|string|min:6',
      'confirmPassword' => 'required|string|same:newPassword',
    ], [
      'newPassword.required' => 'Please enter new password',
      'newPassword.min' => 'Password must be more than 6 characters',
      'confirmPassword.required' => 'Please confirm new password',
      'confirmPassword.same' => 'The password and its confirm are not the same',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    $customer = Customer::query()->findOrFail($request->id);
    $customer->password = $request->newPassword;
    $customer->save();

    Toastr::success('Password updated successfully!');
    return redirect()->back();
  }

  public function store(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'companyName' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
      'mobile' => ['required', 'string', 'digits:10', 'unique:customers,phone'],
      'password' => ['required', 'string', 'min:6'],
      'status' => ['required'],
      'addressLine1' => ['required', 'string', 'max:255'],
      'city' => ['required', 'string', 'max:255'],
      'zip_code' => ['required', 'string', 'max:255'],
    ], [
      'companyName.required' => 'Please enter company name',
      'email.required' => 'Please enter email',
      'email.unique' => 'Email already exists',
      'password.required' => 'Please enter password',
      'password.min' => 'Password must be more than 6 characters',
      'mobile.required' => 'Please enter mobile number',
      'mobile.digits' => 'Mobile number must be 10 digits',
      'mobile.unique' => 'Mobile number already exists',
      'companyName.required' => 'Please enter company name',
      'addressLine1.required' => 'Please enter address line 1',
      'city.required' => 'Please enter city',
      'zip_code.required' => 'Please enter postcode'
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'add')->withInput();
    }

    try {
      DB::beginTransaction();
       
      Customer::create([
        'company_name' => $request->companyName,
        'email' => $request->email,
        'phone' => $request->mobile,
        'password' => $request->password, // ✅ hash password
        'approved_at' => now(),
        'approved_by' => auth()->id(),
        'is_active' => $request->status === 'active' ? 1 : 0,
        'company_address_line1' => $request->addressLine1,
        'company_address_line2' => $request->addressLine2,
        'company_city' => $request->city,
        'company_country' => $request->country,
        'company_zip_code' => $request->zip_code,
        'rep_code' => $request->rep_code ?? null,
        'rep_id' => $request->rep_id ?? null
      ]);

      DB::commit();
      
      Toastr::success('Customer created successfully!');
    } catch (\Exception $e) {
      Toastr::error('Something went wrong');
      Log::error($e);
      DB::rollBack();
    }

    return redirect()->back();
  }

  public function update(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'companyName' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email,' . $request->id],
      'mobile' => ['required', 'string', 'digits:10', 'unique:customers,phone,' . $request->id],
      'password' => ['nullable', 'string', 'min:6'],
      'status' => ['required'],
      'addressLine1' => ['required', 'string', 'max:255'],
      'city' => ['required', 'string', 'max:255'],
      'zip_code' => ['required', 'string', 'max:255'],
    ], [
      'companyName.required' => 'Please enter company name',
      'email.required' => 'Please enter email',
      'email.unique' => 'Email already exists',
      'password.min' => 'Password must be more than 6 characters',
      'mobile.required' => 'Please enter mobile number',
      'mobile.digits' => 'Mobile number must be 10 digits',
      'mobile.unique' => 'Mobile number already exists',
      'companyName.required' => 'Please enter company name',
      'addressLine1.required' => 'Please enter address line 1',
      'city.required' => 'Please enter city',
      'zip_code.required' => 'Please enter postcode'
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'editCustomer')->withInput();
    }

    $customer = Customer::findOrFail($request->id);

    $data = [
      'company_name' => $request->companyName,
      'email' => $request->email,
      'phone' => $request->mobile,
      'is_active' => $request->status === 'active' ? 1 : 0,
      'company_address_line1' => $request->addressLine1,
      'company_address_line2' => $request->addressLine2,
      'company_city' => $request->city,
      'company_country' => $request->country,
      'company_zip_code' => $request->zip_code,
      'rep_code' => $request->rep_code ?? null,
      'rep_id' => $request->rep_id ?? null
    ];

    if ($request->password) {
      $data['password'] = $request->password;
    }

    $customer->update($data);

    Toastr::success('Customer updated successfully!');
    return redirect()->back();
  }
}
