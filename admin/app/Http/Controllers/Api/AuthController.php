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

		// Include soft-deleted users to provide a clear error message
		$customer = Customer::withTrashed()->where('email', $validated['email'])->first();
		if ($customer && method_exists($customer, 'trashed') && $customer->trashed()) {
			return response()->json([
				'success' => false,
				'message' => 'Account has been deleted',
			], 410);
		}

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
            'name' => ['required', 'string', 'max:255'],
            'companyName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'mobile' => ['required', 'string', 'digits:10', 'unique:customers,phone'],
            'password' => ['required', 'string', 'min:6'],
            // address fields
            'addressLine1' => ['required', 'string', 'max:255'],
            'addressLine2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Please enter name',
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
            'zip_code.required' => 'Please enter postcode',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Create customer first (hash password)
        $customer = Customer::create([
            'name' => $data['name'],
            'company_name' => $data['companyName'],
            'email' => $data['email'],
            'phone' => $data['mobile'],
            'password' => bcrypt($data['password']),
            'approved_at' => null,
            'approved_by' => null,
            'is_active' => 1,
        ]);

        // Create default address
        $address = Address::create([
            'customer_id' => $customer->id,
            'name' => $data['name'],
            'address_line1' => $data['addressLine1'],
            'address_line2' => $data['addressLine2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
            'zip_code' => $data['zip_code'],
            'is_default' => 1,
        ]);

        // Attach default address to customer
        $customer->update([
            'address_id' => $address->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'customer_id' => $customer->id,
        ], 201);
    }
}


