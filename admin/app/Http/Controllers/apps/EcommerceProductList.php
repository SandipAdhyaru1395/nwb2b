<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Brian2694\Toastr\Facades\Toastr;

class EcommerceProductList extends Controller
{
  public function index()
  {
    return view('content.apps.app-ecommerce-product-list');
  }

  public function ajaxList(Request $request) {
    
    $products=Product::with(['category:id,name','sub_category:id,name'])->select('id','name','description','category_id','sub_category_id',
            'sku','price','image','is_published')
            ->orderBy('id', 'desc')->get();
    
    $data = [];

    if($products){
      foreach($products as $product){
        $data[] = [
          'id' => $product->id,
          'product_name' => $product->name,
          'product_brand' => $product->description,
          'category' => $product->category->name,
          'sub_category' => $product->sub_category->name,
          'sku' => $product->sku,
          'price' => $product->price,
          'image' => $product->image,
          'is_published' => $product->is_published,
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
