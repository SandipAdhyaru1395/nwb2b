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
    return view('content.apps.app-ecommerce-category-list');
  }

  public function create(Request $request){
    
    $request->validate([
      'categoryTitle' => 'required',
      'categoryStatus' => 'required'
    ]);

    Category::updateOrCreate([
      'name' => $request->categoryTitle],[
      'description'=>$request->categoryDescription,
      'status'=>$request->categoryStatus
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
