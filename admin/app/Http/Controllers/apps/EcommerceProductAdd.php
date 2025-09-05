<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use App\Models\SubCategory;

class EcommerceProductAdd extends Controller
{
  public function index()
  {
    $data['categories'] = Category::orderBy('id', 'desc')->get();
    return view('content.apps.app-ecommerce-product-add',$data);
  }

  public function create(Request $request)
  {
     
    $validated = $request->validate([
      'productTitle' => ['required'],
      'productSku' => ['required','unique:products,sku'],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productDiscountedPrice' => ['nullable', 'numeric', 'min:0', 'lte:productPrice'],
      'productCategory' => ['required'],
      'productSubCategory' => ['required'],
      'productImage' => ['required', 'image', 'mimes:jpeg,png,jpg'],
    ],[
      'productTitle.required' => 'Name is required',
      'productSku.required' => 'SKU is required',
      'productSku.unique' => 'SKU is already taken',
      'productPrice.required' => 'Price is required',
      'productPrice.numeric' => 'Price must be valid number',
      'productPrice.min' => 'Price can not be less than 0',
      'productDiscountedPrice.numeric' => 'Price must be valid number',
      'productDiscountedPrice.min' => 'Price can not be less than 0',
      'productDiscountedPrice.lte' => 'Discounted price can not be greater than price',
      'productCategory.required' => 'Category is required',
      'productSubCategory.required' => 'Sub category is required',
      'productImage.required' => 'Image is required',
      'productImage.image' => 'Must be valid image',
      'productImage.mimes' => 'Only jpg, png, jpeg images are allowed',
    ]);

    $path = $request->file('productImage')->store('products', 'public');
   
    $tags='';

    if($request->productTags != null){
      $productTags = json_decode($request->productTags, true); // decode as array
      $tags = collect($productTags)->pluck('value')->implode(','); 
    }
   
    Product::create([
      'name' => $validated['productTitle'],
      'sku' => $validated['productSku'],
      'barcode' => $request->productBarcode ?? null,
      'price' => $validated['productPrice'] ?? 0,
      'discounted_price' => $validated['productDiscountedPrice'] ?? 0,
      'description' => $request->productDescription ?? null,
      'category_id' => $validated['productCategory'],
      'sub_category_id' => $validated['productSubCategory'],
      'image' => $path,
      'tags'=> $tags ?? '',
      'is_published' => $request->action,
    ]);

    Toastr::success('Product created successfully!');
    return redirect()->route('product-list');
  }

  public function edit($id){
    $data['product'] = Product::findOrFail($id);
    $data['categories'] = Category::orderBy('id', 'desc')->get();

    $data['sub_categories'] = SubCategory::where('category_id', $data['product']->category_id)->orderBy('id', 'desc')->get();
    
    return view('content.apps.app-ecommerce-product-edit',$data);
  }
  
  public function update(Request $request){
    
     $validated = $request->validate([
      'productTitle' => ['required'],
      'productSku' => ['required','unique:products,sku,'.$request->id],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productDiscountedPrice' => ['nullable', 'numeric', 'min:0', 'lte:productPrice'],
      'productCategory' => ['required'],
      'productSubCategory' => ['required'],
      'productImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
    ],[
      'productTitle.required' => 'Name is required',
      'productSku.required' => 'SKU is required',
      'productSku.unique' => 'SKU is already taken',
      'productPrice.required' => 'Price is required',
      'productPrice.numeric' => 'Price must be valid number',
      'productPrice.min' => 'Price can not be less than 0',
      'productDiscountedPrice.numeric' => 'Price must be valid number',
      'productDiscountedPrice.min' => 'Price can not be less than 0',
      'productDiscountedPrice.lte' => 'Discounted price can not be greater than price',
      'productCategory.required' => 'Category is required',
      'productSubCategory.required' => 'Sub category is required',
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
   
    $tags='';

    if($request->productTags != null){
      $productTags = json_decode($request->productTags, true);
      $tags = collect($productTags)->pluck('value')->implode(','); 
    }
   
    Product::find($request->id)->update([
      'name' => $validated['productTitle'],
      'sku' => $validated['productSku'],
      'barcode' => $request->productBarcode ?? null,
      'price' => $validated['productPrice'] ?? 0,
      'discounted_price' => $validated['productDiscountedPrice'] ?? 0,
      'description' => $request->productDescription ?? null,
      'category_id' => $validated['productCategory'],
      'sub_category_id' => $validated['productSubCategory'],
      'image' => $request->file('productImage') != null ? $path : Product::find($request->id)->image,
      'tags'=> $tags ?? '',
      'is_published' => $request->action,
    ]);

    Toastr::success('Product updated successfully!');
    return redirect()->route('product-list');

  }
}
