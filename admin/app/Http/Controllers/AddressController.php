<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Customer;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Store a newly created address.
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(),[
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'is_default' => 'boolean',
        ],[
            'type.required' => 'Please select address type',
            'country.required' => 'Please select country',
            'address_line1.required' => 'Please enter address line 1',
            'city.required' => 'Please enter city',
            'state.required' => 'Please enter state',
            'zip_code.required' => 'Please enter zip code',
        ]);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator,'add')->withInput();
        }
        try {
            DB::beginTransaction();

            // If this is set as default, unset other default addresses for this customer
            $isDefault = $request->boolean('is_default') || $request->input('set_as_default') == '1';
            if ($isDefault) {
                Address::where('customer_id', $request->customer_id)
                    ->update(['is_default' => false]);
            }

            $addressData = $request->all();
            $addressData['is_default'] = $isDefault;
            $address = Address::create($addressData);

            // If this is the first address or set as default, update customer's default address
            if ($isDefault || !Address::where('customer_id', $request->customer_id)->where('id', '!=', $address->id)->exists()) {
                Customer::where('id', $request->customer_id)
                    ->update(['address_id' => $address->id]);
            }

            DB::commit();

            Toastr::success('Address saved successfully');
            return redirect()->back();
            

        } catch (\Exception $e) {
            DB::rollBack();

            Toastr::error('Something went wrong');
            return redirect()->back();
        }
    
    }


    /**
     * Show the form for editing the specified address.
     */
    public function edit(Request $request)
    {
        $address = Address::findOrFail($request->id);

        try {
            return response()->json([
                'success' => true,
                'address' => $address
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'type' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator,'update')->withInput();
        }

        $address = Address::find($request->id);

        try {
            DB::beginTransaction();

            // Handle the checkbox for setting as default
            $isDefault = $request->boolean('is_default') || $request->input('set_as_default') == '1';
            
            // If this is set as default, unset other default addresses for this customer
            if ($isDefault) {
                Address::where('customer_id', $address->customer_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $updateData = $request->all();
            $updateData['is_default'] = $isDefault;
            $address->update($updateData);

            // If this is set as default, update customer's default address
            if ($isDefault) {
                Customer::where('id', $address->customer_id)
                    ->update(['address_id' => $address->id]);
            }

            DB::commit();

            Toastr::success('Address updated successfully');

            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Toastr::error('Something went wrong');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Address $address)
    {
        try {
            DB::beginTransaction();

            $customerId = $address->customer_id;
            $wasDefault = $address->is_default;

            $address->delete();

            // If this was the default address, set another address as default
            if ($wasDefault) {
                $newDefault = Address::where('customer_id', $customerId)->first();
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                    Customer::where('id', $customerId)
                        ->update(['address_id' => $newDefault->id]);
                } else {
                    Customer::where('id', $customerId)
                        ->update(['address_id' => null]);
                }
            }

            DB::commit();

            Toastr::success('Address deleted successfully');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
           Toastr::error('Something went wrong');
            return redirect()->back();
        }
    }

    /**
     * Set address as default.
     */
    public function setDefault(Address $address)
    {
        try {

            DB::beginTransaction();

            // Unset other default addresses for this customer
            Address::where('customer_id', $address->customer_id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);

            // Set this address as default
            $address->update(['is_default' => true]);

            // Update customer's default address
            Customer::where('id', $address->customer_id)
                ->update(['address_id' => $address->id]);

            DB::commit();

            Toastr::success('Address set as default successfully');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Toastr::error('Something went wrong');
            return redirect()->back();
        }
    }
}
