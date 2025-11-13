<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\BrandCategory;
use App\Models\BrandTag;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Models\Product;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
  public function index()
  {
    $data['total_brands_count'] = Brand::all()->count();
    $data['active_brands_count'] = Brand::where('is_active', 1)->count();
    $data['inactive_brands_count'] = Brand::where('is_active', 0)->count();

    return view('content.brand.list', $data);
  }

  public function ajaxList(Request $request)
  {

    $query = Brand::select([
      'id',
      'name as brand',
      'image',
      'is_active'
    ])->with('categories')
      ->orderBy('id', 'desc');

    return DataTables::eloquent($query)
      ->filterColumn('brand', function ($query, $keyword) {
        $query->where('brands.name', 'like', "%{$keyword}%");
      })
      ->orderColumn('brand', function ($query, $order) {
        $query->orderBy('brands.name', $order);
      })
      ->addColumn('categories', function ($brand) {
        return $brand->categories ? $brand->categories->pluck('name')->implode(', ') : '';
      })
      ->toJson();
  }

  public function changeStatus($id)
  {
    $product = Product::findOrFail($id);
    $product->is_published = !$product->is_published;
    $product->save();

    Toastr::success('Product status changed successfully!');
    return redirect()->back();
  }

  public function add()
  {

    $data['brands'] = Brand::orderBy('id', 'desc')->get();
    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();

    return view('content.brand.add', $data);
  }

  public function create(Request $request)
  {

    $validated = $request->validate([
      'brandTitle' => ['required'],
      'brandImage' => ['required', 'image', 'mimes:jpeg,png,jpg,webp'],
      'categories' => ['required'],
    ], [
      'brandTitle.required' => 'Name is required',
      'brandImage.required' => 'Image is required',
      'brandImage.image' => 'Must be valid image',
      'brandImage.mimes' => 'Only jpg, png, jpeg images are allowed',
      'categories.required' => 'Category is required',
    ]);

    $path = $request->file('brandImage')->store('brands', 'public');

    $brand = Brand::create([
      'name' => $validated['brandTitle'],
      'image' => $path,
      'is_active' => $request->brandStatus
    ]);

    if ($request->brandTags != null) {

      $brandTags = json_decode($request->brandTags, true); // decode as array

      foreach ($brandTags as $tag) {

        $tag = Tag::updateOrCreate([
          'name' => $tag['value'],
          'type' => 'categorical'
        ]);

        BrandTag::updateOrCreate([
          'tag_id' => $tag->id,
          'brand_id' => $brand->id
        ]);
      }
    }

    foreach ($request->categories as $category) {
      BrandCategory::create([
        'brand_id' => $brand->id,
        'category_id' => $category
      ]);
    }


    Toastr::success('Brand added successfully!');
    return redirect()->route('brand.list');
  }

  public function edit($id)
  {

    $data['brand'] = Brand::findOrFail($id);

    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();

    $data['brands'] = Brand::orderBy('id', 'desc')->get();

    $data['brandCategories'] = BrandCategory::where('brand_id', $id)->pluck('category_id')->toArray();

    $tag_ids = BrandTag::where('brand_id', $id)->pluck('tag_id')->toArray();

    $brandTags = Tag::select('id', 'name')->whereIn('id', $tag_ids)->get();

    $data['brandTags'] = '';

    if ($brandTags->isNotEmpty()) {
      $data['brandTags'] = $brandTags->implode('name', ', ');
    }

    return view('content.brand.edit', $data);
  }

  public function update(Request $request)
  {

    $validated = $request->validate([
      'brandTitle' => ['required'],
      'brandImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
      'categories' => ['required'],
    ], [
      'brandTitle.required' => 'Name is required',
      'brandImage.image' => 'Must be valid image',
      'brandImage.mimes' => 'Only jpg, png, jpeg images are allowed',
      'categories.required' => 'Category is required',
    ]);

    if ($request->file('brandImage') != null) {

      $image = Brand::find($request->id)->image;

      if ($image) {
        Storage::disk('public')->delete($image);
      }

      $path = $request->file('brandImage')->store('brands', 'public');
    }

    if ($request->brandTags != null) {

      $brandTags = json_decode($request->brandTags, true);

      BrandTag::where('brand_id', $request->id)->delete();

      foreach ($brandTags as $tag) {

        $tag = Tag::updateOrCreate([
          'name' => $tag['value'],
          'type' => 'categorical'
        ]);

        BrandTag::create([
          'tag_id' => $tag->id,
          'brand_id' => $request->id
        ]);
      }
    }

    BrandCategory::where('brand_id', $request->id)->delete();

    foreach ($request->categories as $category) {

      BrandCategory::create([
        'brand_id' => $request->id,
        'category_id' => $category
      ]);
    }

    Brand::find($request->id)->update([
      'name' => $validated['brandTitle'],
      'image' =>  $request->file('brandImage') != null ? $path : Brand::find($request->id)->image,
      'is_active' => $request->brandStatus
    ]);

    Toastr::success('Brand updated successfully!');
    return redirect()->route('brand.list');
  }

  public function delete($id)
  {
    $brand = Brand::findOrFail($id);
    $brand->delete();
    Toastr::success('Brand deleted successfully!');
    return redirect()->back();
  }
}
