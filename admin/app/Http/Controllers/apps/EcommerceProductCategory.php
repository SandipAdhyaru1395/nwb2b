<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\Category;

class EcommerceProductCategory extends Controller
{
  public function index()
  {
    $data['categories']=Category::with('children')->whereNull('parent_id')->get();
    return view('content.apps.app-ecommerce-category-add',$data);
  }

  public function create(Request $request){
    
    if($request->categoryImage){
      $path = $request->file('categoryImage')->store('categories', 'public');
    }else{
      $path = null;
    }

    Category::create(
      ['name' => $request->categoryName,
      'parent_id'=>$request->parentCategory,
      'description'=>$request->categoryDescription,
      'is_active'=>$request->categoryStatus,
      'image_url' => $path
    ]);

    
    Toastr::success('Category created successfully!');

    return redirect()->back();
  }

  public function listAjax(){
    $categories = Category::orderBy('id', 'desc')->get();
    $data=[];
    if($categories){
      foreach($categories as $category){
        $data[]=[
          'id'=>$category->id,
          'categories'=>$category->name,
          'category_detail'=>$category->description,
          'status'=>$category->status
        ];
      }
    }
    return response()->json([
      'data'=>$data
    ]);
  }

  public function getCategoryAjax(Request $request){
    $category = Category::find($request->id);
    return response()->json($category);
  }

  public function update(Request $request){
    $request->validate([
      'categoryTitle' => 'required',
      'categoryStatus' => 'required'
    ]);

    Category::find($request->id)->update([
      'name'=>$request->categoryTitle,
      'description'=>$request->categoryDescription,
      'status'=>$request->categoryStatus
    ]);

    Toastr::success('Category updated successfully!');

    return redirect()->back();
  }
}
