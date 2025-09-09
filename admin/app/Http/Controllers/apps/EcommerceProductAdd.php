<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\CollectionCategory;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use App\Models\Tag;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use App\Models\Collection;
use App\Models\Brand;

class EcommerceProductAdd extends Controller
{
  public function index()
  {
    $data['collections'] = Collection::all();

    return view('content.apps.app-ecommerce-product-add',$data);
  }

  public function create(Request $request)
  {
   
    $validated = $request->validate([
       'collection_id' => ['required'],
      'productTitle' => ['required'],
      'productSku' => ['required','unique:products,sku'],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productImage' => ['required', 'image', 'mimes:jpeg,png,jpg'],
    ],[
      'collection_id.required' => 'Collection is required',
      'productTitle.required' => 'Name is required',
      'productSku.required' => 'SKU is required',
      'productSku.unique' => 'SKU is already taken',
      'productPrice.required' => 'Price is required',
      'productPrice.numeric' => 'Price must be valid number',
      'productPrice.min' => 'Price can not be less than 0',
      'productImage.required' => 'Image is required',
      'productImage.image' => 'Must be valid image',
      'productImage.mimes' => 'Only jpg, png, jpeg images are allowed',
    ]);

    $path = $request->file('productImage')->store('products', 'public');
   
    Product::create([
      'name' => $validated['productTitle'],
      'sku' => $validated['productSku'],
      'description' => $request->productDescription ?? null,
      'price' => $validated['productPrice'] ?? 0,
      'cost_price' => $request->costPrice ?? 0,
      'wallet_credit' => $request->walletCredit ?? 0,
      'image_url' => $path,
      'stock_quantity' => $request->quantity ?? 0,
      'min_order_quantity' => $request->min_order_quantity ?? 0,
      'is_active' => $request->productStatus ?? 0,
      'collection_id' => $request->collection_id,
    ]);

    Toastr::success('Product created successfully!');
    return redirect()->route('product-list');
  }

  public function edit($id){
    
    $data['product'] = Product::findOrFail($id);
    $data['collections'] = Collection::all();

    return view('content.apps.app-ecommerce-product-edit',$data);
  }
  
  public function update(Request $request){
    
    
     $validated = $request->validate([
      'collection_id' => ['required'],
      'productTitle' => ['required'],
      'productSku' => ['required','unique:products,sku,'.$request->id],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
    ],[
      'collection_id.required' => 'Collection is required',
      'productTitle.required' => 'Name is required',
      'productSku.required' => 'SKU is required',
      'productSku.unique' => 'SKU is already taken',
      'productPrice.required' => 'Price is required',
      'productPrice.numeric' => 'Price must be valid number',
      'productPrice.min' => 'Price can not be less than 0',
      'productImage.required' => 'Image is required',
      'productImage.image' => 'Must be valid image',
      'productImage.mimes' => 'Only jpg, png, jpeg images are allowed',
    ]);
     

    if($request->file('productImage') != null){

      $image = Product::find($request->id)->image;  

      if($image){
        Storage::disk('public')->delete($image);
      }

      $path = $request->file('productImage')->store('products', 'public');
    }
   

    Product::find($request->id)->update([
      'name' => $validated['productTitle'],
      'sku' => $validated['productSku'],
      'description' => $request->productDescription ?? null,
      'price' => $validated['productPrice'] ?? 0,
      'cost_price' => $request->costPrice ?? 0,
      'wallet_credit' => $request->walletCredit ?? 0,
      'image_url' => $request->file('productImage') != null ? $path : Product::find($request->id)->image_url,
      'stock_quantity' => $request->quantity ?? 0,
      'min_order_quantity' => $request->min_order_quantity ?? 0,
      'is_active' => $request->productStatus ?? 0,
      'collection_id' => $request->collection_id,
    ]);

    Toastr::success('Product updated successfully!');
    return redirect()->route('product-list');

  }
}
