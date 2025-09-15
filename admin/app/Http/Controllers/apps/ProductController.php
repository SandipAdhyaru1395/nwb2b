<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Models\Brand;
use Illuminate\Support\Str;

class ProductController extends Controller
{
  public function index()
  {
    $data['total_products_count'] = Product::all()->count();
    $data['active_products_count'] = Product::where('is_active',1)->count();
    $data['inactive_products_count'] = Product::where('is_active',0)->count();

    return view('content.product.list',$data);
  }

  public function create(Request $request)
  {
   
    $validated = $request->validate([
      'brands' => ['required'],
      'step' => ['required', 'numeric', 'min:1'],
      'productTitle' => ['required'],
      'productSku' => ['required','unique:products,sku'],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productImage' => ['required', 'image', 'mimes:jpeg,png,jpg'],
    ],[
      'brands.required' => 'Brand is required',
      'step.required' => 'Step quantity is required',
      'step.numeric' => 'Must be valid number',
      'step.min' => 'Must be greater than 0',
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
   
    $product =Product::create([
      'name' => $validated['productTitle'],
      'sku' => $validated['productSku'],
      'step_quantity' => $validated['step'],
      'description' => $request->productDescription ?? null,
      'price' => $validated['productPrice'] ?? 0,
      'cost_price' => $request->costPrice ?? 0,
      'wallet_credit' => $request->walletCredit ?? 0,
      'image_url' => $path,
      'stock_quantity' => $request->quantity ?? 0,
      'min_order_quantity' => $request->min_order_quantity ?? 0,
      'is_active' => $request->productStatus ?? 0,
      'brand_id' => $request->brand_id,
    ]);

    foreach ($request->brands as $brand) {
      ProductBrand::create([
        'product_id' => $product->id,
        'brand_id' => $brand
      ]);
    }

    Toastr::success('Product created successfully!');
    return redirect()->route('product.list');
  }

  public function edit($id){
    
    $data['product'] = Product::findOrFail($id);
    $data['brands'] = Brand::all();

    $data['productBrands'] = ProductBrand::where('product_id', $id)->pluck('brand_id')->toArray();

    return view('content.product.edit',$data);
  }
  
  public function update(Request $request){
    
    
     $validated = $request->validate([
      'brands' => ['required'],
      'step' => ['required', 'numeric', 'min:1'],
      'productTitle' => ['required'],
      'productSku' => ['required','unique:products,sku,'.$request->id],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
    ],[
      'brands.required' => 'Brand is required',
      'step.required' => 'Step quantity is required',
      'step.numeric' => 'Must be valid number',
      'step.min' => 'Must be greater than 0',
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
      'step_quantity' => $validated['step'],
      'description' => $request->productDescription ?? null,
      'price' => $validated['productPrice'] ?? 0,
      'cost_price' => $request->costPrice ?? 0,
      'wallet_credit' => $request->walletCredit ?? 0,
      'image_url' => $request->file('productImage') != null ? $path : Product::find($request->id)->image_url,
      'stock_quantity' => $request->quantity ?? 0,
      'min_order_quantity' => $request->min_order_quantity ?? 0,
      'is_active' => $request->productStatus ?? 0,
    ]);

    ProductBrand::where('product_id', $request->id)->delete();

    foreach ($request->brands as $brand) {

      ProductBrand::create([
        'product_id' => $request->id,
        'brand_id' => $brand
      ]);
    }

    Toastr::success('Product updated successfully!');
    return redirect()->route('product.list');

  }

  public function add(){
    $data['brands'] = Brand::all();
    return view('content.product.add',$data);
  }

  public function ajaxList(Request $request) {
    
    $products=Product::select('id','name','description',
            'sku','price','image_url','is_active')
            ->orderBy('id', 'desc')->get();
    
    $data = [];

    if($products){
      foreach($products as $product){
        $data[] = [
          'id' => $product->id,
          'product_name' => $product->name,
          'product_brand' => Str::limit($product->description,40),
          'sku' => $product->sku,
          'price' => $product->price,
          'image_url' => $product->image_url,
          'is_active' => $product->is_active,
        ];
      }
    }

    return response()->json(['data' => $data]);
  }
 
  public function delete($id)
  {
    $product = Product::findOrFail($id);
    $product->delete();
    Toastr::success('Product deleted successfully!');
    return redirect()->back();
  }
}
