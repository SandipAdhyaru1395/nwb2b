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
use Yajra\DataTables\Facades\DataTables;

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
      'productSku' => ['nullable','unique:products,sku'],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productImage' => ['required', 'image', 'mimes:jpeg,png,jpg'],
    ],[
      'brands.required' => 'Brand is required',
      'step.required' => 'Step quantity is required',
      'step.numeric' => 'Must be valid number',
      'step.min' => 'Must be greater than 0',
      'productTitle.required' => 'Name is required',
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
      'productSku' => ['nullable','unique:products,sku,'.$request->id],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
    ],[
      'brands.required' => 'Brand is required',
      'step.required' => 'Step quantity is required',
      'step.numeric' => 'Must be valid number',
      'step.min' => 'Must be greater than 0',
      'productTitle.required' => 'Name is required',
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
    
   $query = Product::select([
        'id',
        'name as product_name', // 👈 alias here
        'description',
        'sku',
        'price',
        'image_url',
        'is_active'
    ])->orderBy('id', 'desc');


    return DataTables::eloquent($query)
        ->filterColumn('product_name', function($query, $keyword) {
            $query->where('products.name', 'like', "%{$keyword}%");
        })
        ->orderColumn('product_name', function ($query, $order) {
            $query->orderBy('products.name', $order);
        })
        ->editColumn('product_brand', function($product) {
            return Str::limit($product->description, 40);
        })
        ->make(true);
  }

  public function searchAjax(Request $request)
  {
    $q = trim($request->get('q', ''));
    $limit = (int) $request->get('limit', 10);

    $query = Product::select(['id','name','sku','price','image_url','wallet_credit'])
      ->where('is_active',1);

    if ($q !== '') {
      $query->where(function($sub) use ($q) {
        $sub->where('name','like',"%{$q}%")
            ->orWhere('sku','like',"%{$q}%");
      });
    }

    $products = $query->orderBy('id','desc')->limit($limit)->get();

    return response()->json([
      'results' => $products->map(function($p){
        return [
          'id' => $p->id,
          'text' => $p->name . ' (' . $p->sku . ')',
          'price' => $p->price,
          'wallet_credit' => $p->wallet_credit,
          'image_url' => $p->image_url,
        ];
      })
    ]);
  }
 
  public function delete($id)
  {
    $product = Product::findOrFail($id);
    $product->delete();
    Toastr::success('Product deleted successfully!');
    return redirect()->back();
  }
}
