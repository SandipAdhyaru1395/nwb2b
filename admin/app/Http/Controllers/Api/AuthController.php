<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;
use App\Models\Address;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
{
	public function login(Request $request)
	{
		$validated = $request->validate([
			'email' => 'required|email',
			'password' => 'required|string',
			'device_name' => 'nullable|string',
		]);

		$customer = Customer::where('email', $validated['email'])->first();
		if (!$customer || !Hash::check($validated['password'], $customer->password)) {
			return response()->json([
				'success' => false,
				'message' => 'Invalid credentials',
			], 401);
		}

		if (!(int)($customer->is_active ?? 0)) {
			return response()->json([
				'success' => false,
				'message' => 'Account is inactive',
			], 403);
		}

		$deviceName = $validated['device_name'] ?? $request->userAgent() ?? 'api-client';
		$token = $customer->createToken($deviceName)->plainTextToken;

		$customer->last_login = now();
		$customer->save();

		return response()->json([
			'success' => true,
			'token' => $token,
			'customer' => [
				'id' => $customer->id,
				'name' => $customer->name,
				'email' => $customer->email,
			],
		]);
	}

	public function logout(Request $request)
	{
		$user = $request->user();
		if ($user && method_exists($user, 'currentAccessToken')) {
			$user->currentAccessToken()->delete();
		}
		return response()->json([
			'success' => true,
			'message' => 'Logged out',
		]);
	}

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:customers,email'],
            'password' => ['required','string','min:6','confirmed'],
            'mobile' => ['required','string','digits:10','unique:customers,phone'],
            // extra fields from frontend
            'company' => ['nullable','string','max:255'],
            'invoice_address_line1' => ['nullable','string','max:255'],
            'invoice_address_line2' => ['nullable','string','max:255'],
            'invoice_city' => ['nullable','string','max:255'],
            'invoice_county' => ['nullable','string','max:255'],
            'invoice_postcode' => ['nullable','string','max:50'],
            'rep_code' => ['nullable','string','max:50'],
        ], [
            'name.required' => 'Please enter name',
            'email.required' => 'Please enter email',
            'email.unique' => 'Email already exists',
            'password.required' => 'Please enter password',
            'password.min' => 'Password must be more than 6 characters',
            'mobile.required' => 'Please enter mobile number',
            'mobile.digits' => 'Mobile number must be 10 digits',
            'mobile.unique' => 'Mobile number already exists',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $addressId = null;
        if (!empty($data['invoice_address_line1'])) {
            $address = Address::create([
                'line1' => $data['invoice_address_line1'] ?? null,
                'line2' => $data['invoice_address_line2'] ?? null,
                'city' => $data['invoice_city'] ?? null,
                'state' => $data['invoice_county'] ?? null,
                'postcode' => $data['invoice_postcode'] ?? null,
                'country' => 'GB',
            ]);
            $addressId = $address->id;
        }

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['mobile'],
            'company_name' => $data['company'] ?? '',
            'approved_at' => Carbon::now(),
            'approved_by' => null,
            'is_active' => 1,
            'address_id' => $addressId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'customer_id' => $customer->id,
        ], 201);
    }
}


