<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->with(['childrenRecursive', 'brands.products'])
            ->orderBy('sort_order', 'asc')
            ->get();

        $formatted = $categories->map(fn($category) => $this->formatCategoryRecursive($category))->values();

        return response()->json(['categories' => $formatted]);
    }

    protected function formatCategoryRecursive($category)
    {
        $children = $category->relationLoaded('children') ? $category->children : $category->children()->get();
        $hasChildren = $children && $children->isNotEmpty();

        if ($hasChildren) {
            return [
                'name' => $category->name,
                'subcategories' => $children->map(fn($child) => $this->formatCategoryRecursive($child))->values(),
            ];
        }

        $brands = $category->brands ?? collect();

        return [
            'name' => $category->name,
            'subcategories' => $brands->map(function ($brand) {
                $products = $brand->products ?? collect();
                return [
                    'name' => $brand->name,
                    'products' => $products->map(fn($product) => $this->formatProduct($product))->values(),
                ];
            })->values(),
        ];
    }

    protected function formatProduct($product)
    {
        $image = $product->image_url ?? ($product->image ?? null);
        $imageUrl = $image ? (str_starts_with($image, 'http') ? $image : asset($image)) : asset('assets/img/icons/misc/search-jpg.png');

        $priceNumber = is_numeric($product->price) ? (float) $product->price : 0;
        $discountNumber = isset($product->discount) && is_numeric($product->discount) ? (float) $product->discount : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'image' => $imageUrl,
            'price' => '£' . number_format($priceNumber, 2),
            'discount' => $discountNumber !== null ? ('£' . number_format($discountNumber, 2)) : null,
        ];
    }

}
