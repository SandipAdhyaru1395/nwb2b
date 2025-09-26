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

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $user->id,
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'wallet_balance' => (float) ($user->credit_balance ?? 0),
            ],
        ]);
    }
}


