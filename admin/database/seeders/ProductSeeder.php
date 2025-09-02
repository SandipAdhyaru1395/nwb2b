<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    // ---------------- Electronics ----------------
        $electronics = Category::create([
            'name' => 'Electronics',
            'status' => 'active'
        ]);

        $mobiles = SubCategory::create([
            'category_id' => $electronics->id,
            'name' => 'Mobiles',
            'status' => 'active'
        ]);

        Product::create([
            'name' => 'iPhone 14 Pro',
            'sku' => 'iphone-14-pro',
            'barcode' => '0123-4567',
            'price' => '120000',
            'discounted_price' => '110000',
            'description' => 'Apple iPhone 14 Pro',
            'category_id' => $electronics->id,
            'sub_category_id' => $mobiles->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        Product::create([
            'name' => 'Samsung Galaxy S23',
            'sku' => 'samsung-galaxy-s23',
            'barcode' => '0123-4568',
            'price' => '95000',
            'discounted_price' => '88000',
            'description' => 'Samsung Galaxy S23 Smartphone',
            'category_id' => $electronics->id,
            'sub_category_id' => $mobiles->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        Product::create([
            'name' => 'OnePlus 11',
            'sku' => 'oneplus-11',
            'barcode' => '0123-4569',
            'price' => '60000',
            'discounted_price' => '55000',
            'description' => 'OnePlus 11 Smartphone',
            'category_id' => $electronics->id,
            'sub_category_id' => $mobiles->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        $laptops = SubCategory::create([
            'category_id' => $electronics->id,
            'name' => 'Laptops',
            'status' => 'active'
        ]);

        Product::create([
            'name' => 'MacBook Air M2',
            'sku' => 'macbook-air-m2',
            'barcode' => '1001-2001',
            'price' => '105000',
            'discounted_price' => '99000',
            'description' => 'Apple MacBook Air M2',
            'category_id' => $electronics->id,
            'sub_category_id' => $laptops->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        Product::create([
            'name' => 'Dell XPS 13',
            'sku' => 'dell-xps-13',
            'barcode' => '1001-2002',
            'price' => '95000',
            'discounted_price' => '90000',
            'description' => 'Dell XPS 13 Laptop',
            'category_id' => $electronics->id,
            'sub_category_id' => $laptops->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        Product::create([
            'name' => 'Lenovo ThinkPad X1',
            'sku' => 'lenovo-thinkpad-x1',
            'barcode' => '1001-2003',
            'price' => '85000',
            'discounted_price' => '80000',
            'description' => 'Lenovo ThinkPad X1 Laptop',
            'category_id' => $electronics->id,
            'sub_category_id' => $laptops->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        $televisions = SubCategory::create([
            'category_id' => $electronics->id,
            'name' => 'Televisions',
            'status' => 'active'
        ]);

        Product::create([
            'name' => 'Sony Bravia 55"',
            'sku' => 'sony-bravia-55',
            'barcode' => '2001-3001',
            'price' => '75000',
            'discounted_price' => '70000',
            'description' => 'Sony Bravia 55 Inch TV',
            'category_id' => $electronics->id,
            'sub_category_id' => $televisions->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        Product::create([
            'name' => 'LG OLED 65"',
            'sku' => 'lg-oled-65',
            'barcode' => '2001-3002',
            'price' => '150000',
            'discounted_price' => '140000',
            'description' => 'LG OLED 65 Inch TV',
            'category_id' => $electronics->id,
            'sub_category_id' => $televisions->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        Product::create([
            'name' => 'Samsung QLED 50"',
            'sku' => 'samsung-qled-50',
            'barcode' => '2001-3003',
            'price' => '65000',
            'discounted_price' => '60000',
            'description' => 'Samsung QLED 50 Inch TV',
            'category_id' => $electronics->id,
            'sub_category_id' => $televisions->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        // ---------------- Fashion ----------------
        $fashion = Category::create([
            'name' => 'Fashion',
            'status' => 'active'
        ]);

        $men = SubCategory::create([
            'category_id' => $fashion->id,
            'name' => 'Men',
            'status' => 'active'
        ]);

        Product::create([
            'name' => 'Levi’s Slim Fit Jeans',
            'sku' => 'levis-jeans',
            'barcode' => '3001-4001',
            'price' => '3499',
            'discounted_price' => '2999',
            'description' => 'Men Slim Fit Jeans',
            'category_id' => $fashion->id,
            'sub_category_id' => $men->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        Product::create([
            'name' => 'Nike Air Max Shoes',
            'sku' => 'nike-air-max',
            'barcode' => '3001-4002',
            'price' => '7999',
            'discounted_price' => '6999',
            'description' => 'Men Sports Shoes',
            'category_id' => $fashion->id,
            'sub_category_id' => $men->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

        Product::create([
            'name' => 'Raymond Formal Shirt',
            'sku' => 'raymond-shirt',
            'barcode' => '3001-4003',
            'price' => '2499',
            'discounted_price' => '2199',
            'description' => 'Men Formal Shirt',
            'category_id' => $fashion->id,
            'sub_category_id' => $men->id,
            'image' => null,
            'tags'=> '',
            'is_published' => 1,
        ]);

    }
}
