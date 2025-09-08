<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;

class EcommerceProductList extends Controller
{
  public function index()
  {
    $data['total_products_count'] = Product::all()->count();
    $data['active_products_count'] = Product::where('is_active',1)->count();
    $data['inactive_products_count'] = Product::where('is_active',0)->count();

    return view('content.apps.app-ecommerce-product-list',$data);
  }

  public function ajaxList(Request $request) {
    
    $products=Product::with(['categories'])->select('id','name','description',
            'sku','price','image_url','is_active')
            ->orderBy('id', 'desc')->get();
    
    $data = [];

    if($products){
      foreach($products as $product){
        $data[] = [
          'id' => $product->id,
          'product_name' => $product->name,
          'product_brand' => Str::limit($product->description,40),
          // 'product_brand' => $product->description,
          'categories' => $product->categories->pluck('name')->implode(', '),
          'sku' => $product->sku,
          'price' => $product->price,
          'image_url' => $product->image_url,
          'is_active' => $product->is_active,
        ];
      }
    }

    return response()->json(['data' => $data]);
  }

  public function changeStatus($id)
  {
    $product = Product::findOrFail($id);
    $product->is_published = !$product->is_published;
    $product->save();

    Toastr::success('Product status changed successfully!');
    return redirect()->back();
  }
}
