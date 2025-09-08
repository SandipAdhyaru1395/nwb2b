<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use App\Models\Tag;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use App\Models\SubCategory;
use App\Models\Brand;

class EcommerceProductAdd extends Controller
{
  public function index()
  {
    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();

    $data['brands'] = Brand::orderBy('id', 'desc')->get();
    return view('content.apps.app-ecommerce-product-add',$data);
  }

  public function create(Request $request)
  {
   
    $validated = $request->validate([
      'productTitle' => ['required'],
      'productSku' => ['required','unique:products,sku'],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productImage' => ['required', 'image', 'mimes:jpeg,png,jpg'],
      'brand_id' => ['required'],
      'categories' => ['required'],
    ],[
      'productTitle.required' => 'Name is required',
      'productSku.required' => 'SKU is required',
      'productSku.unique' => 'SKU is already taken',
      'productPrice.required' => 'Price is required',
      'productPrice.numeric' => 'Price must be valid number',
      'productPrice.min' => 'Price can not be less than 0',
      'productImage.required' => 'Image is required',
      'productImage.image' => 'Must be valid image',
      'productImage.mimes' => 'Only jpg, png, jpeg images are allowed',
      'brand_id.required' => 'Brand is required',
      'categories.required' => 'Category is required',
    ]);

    $path = $request->file('productImage')->store('products', 'public');
   
    $product = Product::create([
      'name' => $validated['productTitle'],
      'sku' => $validated['productSku'],
      'description' => $request->productDescription ?? null,
      'price' => $validated['productPrice'] ?? 0,
      'cost_price' => $request->costPrice ?? 0,
      'image_url' => $path,
      'stock_quantity' => $request->quantity ?? 0,
      'min_order_quantity' => $request->min_order_quantity ?? 0,
      'brand_id' => $request->brand_id ?? null,
      'is_active' => $request->productStatus ?? 0,
      'is_new'=> ($request->isNew=='on')?1:0,
    ]);


    if($request->productTags != null){
    
      $productTags = json_decode($request->productTags, true); // decode as array
    
      foreach($productTags as $tag){

        $tag = Tag::updateOrCreate([
          'name' => $tag['value'],
          'type' => 'categorical'
        ]);

        ProductTag::updateOrCreate([
          'tag_id' => $tag->id,
          'product_id' => $product->id
        ]);
      } 
      
    }

    foreach($request->categories as $category){
      ProductCategory::create([
        'product_id' => $product->id,
        'category_id' => $category
      ]);
    }

    Toastr::success('Product created successfully!');
    return redirect()->route('product-list');
  }

  public function edit($id){
    
    $data['product'] = Product::findOrFail($id);
    
    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();

    $data['brands'] = Brand::orderBy('id', 'desc')->get();
    
    $data['productCategories'] = ProductCategory::where('product_id', $id)->pluck('category_id')->toArray();

    $tag_ids = ProductTag::where('product_id', $id)->pluck('tag_id')->toArray();

    $productTags =Tag::select('id', 'name')->whereIn('id', $tag_ids)->get();

    $data['productTags'] = '';

    if($productTags->isNotEmpty()){
      $data['productTags'] =$productTags->implode('name',', ');
    }

    return view('content.apps.app-ecommerce-product-edit',$data);
  }
  
  public function update(Request $request){
    
    
     $validated = $request->validate([
      'productTitle' => ['required'],
      'productSku' => ['required','unique:products,sku,'.$request->id],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
      'brand_id' => ['required'],
      'categories' => ['required'],
    ],[
      'productTitle.required' => 'Name is required',
      'productSku.required' => 'SKU is required',
      'productSku.unique' => 'SKU is already taken',
      'productPrice.required' => 'Price is required',
      'productPrice.numeric' => 'Price must be valid number',
      'productPrice.min' => 'Price can not be less than 0',
      'productImage.required' => 'Image is required',
      'productImage.image' => 'Must be valid image',
      'productImage.mimes' => 'Only jpg, png, jpeg images are allowed',
      'brand_id.required' => 'Brand is required',
      'categories.required' => 'Category is required',
    ]);
     

    if($request->file('productImage') != null){

      $image = Product::find($request->id)->image;  

      if($image){
        Storage::disk('public')->delete($image);
      }

      $path = $request->file('productImage')->store('products', 'public');
    }
   
    if($request->productTags != null){
      
      $productTags = json_decode($request->productTags, true);

      ProductTag::where('product_id', $request->id)->delete();
      
      foreach($productTags as $tag){

        $tag = Tag::updateOrCreate([
          'name' => $tag['value'],
          'type' => 'categorical'
        ]);

        ProductTag::create([
          'tag_id' => $tag->id,
          'product_id' => $request->id
        ]);
      }
    }
   
    ProductCategory::where('product_id', $request->id)->delete();

    foreach($request->categories as $category){
      
      ProductCategory::create([
        'product_id' => $request->id,
        'category_id' => $category
      ]);
    }

    Product::find($request->id)->update([
      'name' => $validated['productTitle'],
      'sku' => $validated['productSku'],
      'description' => $request->productDescription ?? null,
      'price' => $validated['productPrice'] ?? 0,
      'cost_price' => $request->costPrice ?? 0,
      'image_url' => $request->file('productImage') != null ? $path : Product::find($request->id)->image_url,
      'stock_quantity' => $request->quantity ?? 0,
      'min_order_quantity' => $request->min_order_quantity ?? 0,
      'brand_id' => $request->brand_id ?? null,
      'is_active' => $request->productStatus ?? 0,
      'is_new'=> ($request->isNew=='on')?1:0,
    ]);

    Toastr::success('Product updated successfully!');
    return redirect()->route('product-list');

  }
}
