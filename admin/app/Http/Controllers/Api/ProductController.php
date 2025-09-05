<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::with(['subcategories.products'])->get();

        $formatted = $categories->map(function ($category) {
            return [
                'name' => $category->name,
                'subcategories' => $category->subcategories->map(function ($sub) {
                    return [
                        'name' => $sub->name,
                        'products' => $sub->products->map(function ($product) {
                            return [
                                'id' => $product->id,
                                'name' => $product->name,
                                'image' => ($product->image) ? asset('storage/' . $product->image) : asset('assets/img/icons/misc/search-jpg.png'),
                                'price' => '£' . number_format($product->price, 2),
                                'discount' => $product->discount ? '£' . number_format($product->discount, 2) : null,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return response()->json(['categories' => $formatted]);
    }
}
