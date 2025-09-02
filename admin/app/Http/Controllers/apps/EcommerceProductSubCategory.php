<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;

class EcommerceProductSubCategory extends Controller
{
  public function index()
  {
    $data['categories'] = Category::orderBy('id', 'desc')->get();
    return view('content.apps.app-ecommerce-sub-category-list',$data);
  }

  public function create(Request $request){
    
    $request->validate([
      'category_id' => 'required',
      'subcategoryTitle' => 'required',
      'subcategoryStatus' => 'required'
    ]);

    SubCategory::updateOrCreate([
      'category_id' => $request->category_id,
      'name' => $request->subcategoryTitle
      ],[
      'description'=>$request->subcategoryDescription,
      'status'=>$request->subcategoryStatus
    ]);

    Toastr::success('Sub Category created successfully!');

    return redirect()->back();
  }

  public function listAjax(){
    
    $sub_categories = SubCategory::orderBy('id', 'desc')->get();
    $data=[];
    if($sub_categories){
      foreach($sub_categories as $sub_category){

        $data[]=[
          'id'=>$sub_category->id,
          'sub_category'=> $sub_category->name,
          'sub_category_desc'=> $sub_category->description,
          'category' => $sub_category->category->name,
          'status'=> $sub_category->status
        ];
      }
    }
    
    return response()->json([
      'data'=>$data
    ]);
  }

  public function getSubCategoryAjax(Request $request){

    $sub_category = SubCategory::with(['category:id,name'])->select('id', 'name','category_id','description', 'status')->whereId($request->id)->first();
    
    return response()->json($sub_category);
  }

  public function update(Request $request){

    $request->validate([
      'subcategoryTitle' => 'required',
      'category_id' => 'required',
      'subcategoryStatus' => 'required'
    ]);

    SubCategory::find($request->id)->update([
      'name'=>$request->subcategoryTitle,
      'category_id' => $request->category_id,
      'description'=>$request->subcategoryDescription,
      'status'=>$request->subcategoryStatus
    ]);

    Toastr::success('Category updated successfully!');

    return redirect()->back();
  }

  public function getSubCategoryListByCategoryAjax(Request $request){
    $sub_categories = SubCategory::select('id', 'name')->where('category_id', $request->cat_id)->get();

    return response()->json($sub_categories);
  }
}
