<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
  // public function index()
  // {
  //   $data['categories']=Category::with('children')->whereNull('parent_id')->get();
  //   return view('content.category.add',$data);
  // }

  public function index()
  {
    $data['total_categories_count'] = Category::all()->count();
    $data['active_categories_count'] = Category::where('is_active', 1)->count();
    $data['inactive_categories_count'] = Category::where('is_active', 0)->count();

    return view('content.category.list', $data);
  }

  public function ajaxList(Request $request)
  {

    $categories = Category::orderBy('id', 'desc')->get();

    $data = [];

    if ($categories) {

      foreach ($categories as $category) {
        $data[] = [
          'id' => $category->id,
          'name' => $category->name,
          'parent_category' => $category->parent?->name ?? '-',
          'child_categories' => ($category->children->isNotEmpty()) ? $category->children->implode('name', ', ') : '-',
          'image' => $category->image,
          'is_active' => $category->is_active,
        ];
      }
    }

    return response()->json(['data' => $data]);
  }

  public function add(){

    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();
    return view('content.category.add', $data);
  }
  public function edit($id){

    $data['main_category'] = Category::findOrFail($id);

    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();

    return view('content.category.edit', $data);
  }

  public function create(Request $request)
  {

    $request->validate([
      'categoryName' => 'required',
      'categoryStatus' => 'required',
    ], [
      'categoryName.required' => 'Name is required',
      'categoryStatus.required' => 'Status is required',
    ]);

    
    Category::create(
      [
        'name' => $request->categoryName,
        'parent_id' => $request->parentCategory,
        'description' => $request->categoryDescription,
        'is_special' => (isset($request->is_special) && $request->is_special == 'on' && $request->parentCategory == null) ? true : false,
        'sort_order' => $request->sortOrder ?? 1,
        'is_active' => $request->categoryStatus,
      ]
    );


    Toastr::success('Category created successfully!');

    return redirect()->route('category.list');
  }

  public function listAjax()
  {
    $categories = Category::orderBy('id', 'desc')->get();
    $data = [];
    if ($categories) {
      foreach ($categories as $category) {
        $data[] = [
          'id' => $category->id,
          'categories' => $category->name,
          'category_detail' => $category->description,
          'status' => $category->status
        ];
      }
    }
    return response()->json([
      'data' => $data
    ]);
  }

  public function getCategoryAjax(Request $request)
  {
    $category = Category::find($request->id);
    return response()->json($category);
  }

  public function update(Request $request)
  {
    $request->validate([
      'categoryName' => 'required',
      'categoryStatus' => 'required'
    ]);

    $data=[
      'name' => $request->categoryName,
      'parent_id' => $request->parentCategory,
      'description' => $request->categoryDescription,
      'is_special' => (isset($request->is_special) && $request->is_special == 'on' && $request->parentCategory == null) ? true : false,
      'is_active' => $request->categoryStatus
    ];

    if(isset($request->sortOrder) && $request->sortOrder > 0){
      $data['sort_order'] = $request->sortOrder;
    }

    Category::find($request->id)->update($data);

    Toastr::success('Category updated successfully!');

    return redirect()->route('category.list');
  }

  public function delete($id)
  {
    $category = Category::findOrFail($id);
    $category->delete();
    Toastr::success('Category deleted successfully!');
    return redirect()->back();
  }
}
