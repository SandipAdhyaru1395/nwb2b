<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class EcommerceProductAdd extends Controller
{
  public function index()
  {
    return view('content.apps.app-ecommerce-product-add');
  }

  public function store(Request $request)
  {
    return redirect()->back();
    $validated = $request->validate([
      'productTitle' => ['required', 'string', 'max:255'],
      'productSku' => ['required', 'string', 'max:64', 'unique:products,sku'],
      'productBarcode' => ['nullable', 'string', 'max:64'],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productDiscountedPrice' => ['nullable', 'numeric', 'min:0'],
      'quantity' => ['nullable', 'integer', 'min:0'],
      'category' => ['nullable', 'string', 'max:255'],
      'vendor' => ['nullable', 'string', 'max:255'],
      'status' => ['nullable', 'string', 'in:Publish,Scheduled,Inactive'],
    ]);

    // Product::create([
    //   'name' => $validated['productTitle'],
    //   'sku' => $validated['productSku'],
    //   'barcode' => $validated['productBarcode'] ?? null,
    //   'category' => $validated['category'] ?? null,
    //   'description' => null,
    //   'image' => null,
    //   'quantity' => (int)($validated['quantity'] ?? 0),
    //   'status' => $validated['status'] ?? 'Publish',
    //   'price' => $validated['productPrice'],
    //   'discounted_price' => $validated['productDiscountedPrice'] ?? null,
    // ]);

    // return redirect()->route('app-ecommerce-product-list')->with('success', 'Product created');
  }
}
