<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Load the default address relationship
        $user->load('defaultAddress');
        $defaultAddress = $user->defaultAddress;

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $user->id,
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
                'wallet_balance' => (float) ($user->credit_balance ?? 0),
                'company_name' => $user->company_name ?? null,
                'address_line1' => $defaultAddress?->address_line1 ?? null,
                'address_line2' => $defaultAddress?->address_line2 ?? null,
                'city' => $defaultAddress?->city ?? null,
                'country' => $defaultAddress?->country ?? null,
                'state' => $defaultAddress?->state ?? null,
                'postcode' => $defaultAddress?->zip_code ?? null,
            ],
        ]);
    }

    public function updateCompanyDetails(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'city' =>  ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:255'],
        ]);

        // Update customer company name
        $user->update([
            'company_name' => $request->company_name,
        ]);

        // Update or create default address
        $defaultAddress = $user->defaultAddress;
        
        if ($defaultAddress) {
            // Update existing default address
            $defaultAddress->update([
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city' => $request->city,
                'country' => $request->country,
                'state' => $request->state,
                'zip_code' => $request->postcode,
            ]);
        } else {
            // Create new default address
            $user->addresses()->create([
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city' => $request->city,
                'country' => $request->country,
                'state' => $request->state,
                'zip_code' => $request->postcode,
                'is_default' => true,
            ]);
            
            // Update customer's default address reference
            $user->update(['address_id' => $user->addresses()->latest()->first()->id]);
        }

        // Reload the relationship
        $user->load('defaultAddress');
        $defaultAddress = $user->defaultAddress;

        return response()->json([
            'success' => true,
            'message' => 'Company details updated successfully',
            'customer' => [
                'id' => $user->id,
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
                'wallet_balance' => (float) ($user->credit_balance ?? 0),
                'company_name' => $user->company_name ?? null,
                'address_line1' => $defaultAddress?->address_line1 ?? null,
                'address_line2' => $defaultAddress?->address_line2 ?? null,
                'city' => $defaultAddress?->city ?? null,
                'country' => $defaultAddress?->country ?? null,
                'state' => $defaultAddress?->state ?? null,
                'postcode' => $defaultAddress?->zip_code ?? null,
            ],
        ]);
    }
}


