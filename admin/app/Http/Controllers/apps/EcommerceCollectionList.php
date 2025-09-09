<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\CollectionCategory;
use App\Models\CollectionTag;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Models\Product;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EcommerceCollectionList extends Controller
{
  public function index()
  {
    $data['total_collections_count'] = Collection::all()->count();
    $data['active_collections_count'] = Collection::where('is_active', 1)->count();
    $data['inactive_collections_count'] = Collection::where('is_active', 0)->count();

    return view('content.apps.app-ecommerce-collection-list', $data);
  }

  public function ajaxList(Request $request)
  {

    $collections = Collection::with(['categories'])
      ->orderBy('id', 'desc')->get();

    $data = [];

    if ($collections) {

      foreach ($collections as $collection) {
        $data[] = [
          'id' => $collection->id,
          'collection_name' => $collection->name,
          'categories' => $collection->categories->pluck('name')->implode(', '),
          'image' => $collection->image,
          'brand' => $collection->brand->name,
          // 'is_new' => $collection->is_new,
          // 'is_hot' => $collection->is_hot,
          // 'is_sale' => $collection->is_sale,
          'is_active' => $collection->is_active,
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

  public function add()
  {

    $data['brands'] = Brand::orderBy('id', 'desc')->get();
    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();

    return view('content.apps.app-ecommerce-collection-add', $data);
  }

  public function create(Request $request)
  {

    $validated = $request->validate([
      'collectionTitle' => ['required'],
      'collectionImage' => ['required', 'image', 'mimes:jpeg,png,jpg'],
      'brand_id' => ['required'],
      'categories' => ['required'],
    ], [
      'collectionTitle.required' => 'Name is required',
      'collectionImage.required' => 'Image is required',
      'collectionImage.image' => 'Must be valid image',
      'collectionImage.mimes' => 'Only jpg, png, jpeg images are allowed',
      'brand_id.required' => 'Brand is required',
      'categories.required' => 'Category is required',
    ]);

    $path = $request->file('collectionImage')->store('collections', 'public');

    $collection = Collection::create([
      'name' => $validated['collectionTitle'],
      'image' => $path,
      'is_new' => ($request->isNew == 'on') ? true : false,
      'is_hot' => ($request->isHot == 'on') ? true : false,
      'is_sale' => ($request->isSale == 'on') ? true : false,
      'brand_id' => $validated['brand_id'],
      'categories' => $validated['categories'],
      'is_active' => $request->collectionStatus
    ]);

    if ($request->collectionTags != null) {

      $collectionTags = json_decode($request->collectionTags, true); // decode as array

      foreach ($collectionTags as $tag) {

        $tag = Tag::updateOrCreate([
          'name' => $tag['value'],
          'type' => 'categorical'
        ]);

        CollectionTag::updateOrCreate([
          'tag_id' => $tag->id,
          'collection_id' => $collection->id
        ]);
      }
    }

    foreach ($request->categories as $category) {
      CollectionCategory::create([
        'collection_id' => $collection->id,
        'category_id' => $category
      ]);
    }


    Toastr::success('Collection added successfully!');
    return redirect()->route('collection-list');
  }

  public function edit($id)
  {

    $data['collection'] = Collection::findOrFail($id);

    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();

    $data['brands'] = Brand::orderBy('id', 'desc')->get();

    $data['collectionCategories'] = CollectionCategory::where('collection_id', $id)->pluck('category_id')->toArray();

    $tag_ids = CollectionTag::where('collection_id', $id)->pluck('tag_id')->toArray();

    $collectionTags = Tag::select('id', 'name')->whereIn('id', $tag_ids)->get();

    $data['collectionTags'] = '';

    if ($collectionTags->isNotEmpty()) {
      $data['collectionTags'] = $collectionTags->implode('name', ', ');
    }

    return view('content.apps.app-ecommerce-collection-edit', $data);
  }

  public function update(Request $request)
  {

    $validated = $request->validate([
      'collectionTitle' => ['required'],
      'collectionImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
      'brand_id' => ['required'],
      'categories' => ['required'],
    ], [
      'collectionTitle.required' => 'Name is required',
      'collectionImage.image' => 'Must be valid image',
      'collectionImage.mimes' => 'Only jpg, png, jpeg images are allowed',
      'brand_id.required' => 'Brand is required',
      'categories.required' => 'Category is required',
    ]);

    if ($request->file('collectionImage') != null) {

      $image = Collection::find($request->id)->image;

      if ($image) {
        Storage::disk('public')->delete($image);
      }

      $path = $request->file('collectionImage')->store('collections', 'public');
    }

    if ($request->collectionTags != null) {

      $collectionTags = json_decode($request->collectionTags, true);

      CollectionTag::where('collection_id', $request->id)->delete();

      foreach ($collectionTags as $tag) {

        $tag = Tag::updateOrCreate([
          'name' => $tag['value'],
          'type' => 'categorical'
        ]);

        CollectionTag::create([
          'tag_id' => $tag->id,
          'collection_id' => $request->id
        ]);
      }
    }

    CollectionCategory::where('collection_id', $request->id)->delete();

    foreach ($request->categories as $category) {

      CollectionCategory::create([
        'collection_id' => $request->id,
        'category_id' => $category
      ]);
    }

    Collection::find($request->id)->update([
      'name' => $validated['collectionTitle'],
      'image' =>  $request->file('collectionImage') != null ? $path : Collection::find($request->id)->image,
      'is_new' => ($request->isNew == 'on') ? true : false,
      'is_hot' => ($request->isHot == 'on') ? true : false,
      'is_sale' => ($request->isSale == 'on') ? true : false,
      'brand_id' => $validated['brand_id'],
      'is_active' => $request->collectionStatus
    ]);

    Toastr::success('Collection updated successfully!');
    return redirect()->route('collection-list');
  }
}
