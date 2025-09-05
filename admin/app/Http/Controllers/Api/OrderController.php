<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderController extends Controller
{
    public function index(Request $request){
        $limit = (int)($request->query('limit', 10));
        $orders = Order::
            latest('created_at')
            ->take($limit)
            ->get();

        $data = $orders->map(function(Order $order){
            return [
                'order_number' => $order->order_number,
                'ordered_at' => optional($order->created_at)->format('H:i d/m/Y'),
                'payment_status' => strtoupper($order->payment_status ?? 'PENDING'),
                'fulfillment_status' => strtoupper($order->status ?? 'PENDING'),
                'units' => (int)($order->units_count ?? 0),
                'skus' => (int)($order->skus_count ?? 0),
                'total_paid' => (float)($order->total ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'orders' => $data,
        ]);
    }

    public function store(Request $request){
        try {
            // Validate the request
            $request->validate([
                'items' => 'required|array',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'total' => 'required|numeric|min:0',
                'units' => 'required|integer|min:1',
                'skus' => 'required|integer|min:1'
            ]);
    
            $items = $request->input('items');
            $total = $request->input('total');
            $units = $request->input('units');
            $skus = $request->input('skus');
            
            $total = 0;
            
            OrderItem::whereNull('order_id')->delete();
            
            foreach($items as $item){

                $product = Product::find($item['product_id']);
                
                if(!$product){
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found',
                    ], 200);
                }
                

                OrderItem::create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'discounted_price' => $product->discounted_price,
                    'total' => ($product->discounted_price != 0) ? $product->discounted_price * $item['quantity'] : $product->price * $item['quantity'],
                    'discount' => ($product->discounted_price != 0) ? ($product->discounted_price - $product->discount) * $item['quantity'] : 0,
                    'product_info' => $product
                ]);
                
                $total += $product->price * $item['quantity'];
            }
           
            if($total != $request->input('total')){
                return response()->json([
                    'success' => false,
                    'message' => 'Prices have been updated. Update your cart and try again.',
                ], 200);
            }

            $orderNumber = 'ORD-' . strtoupper(uniqid());
                
            $order = Order::create([
                'order_number' => $orderNumber,
                'total' => $total,
                'units_count' => $units,
                'skus_count' => $skus,
                'items_count' => count($items),
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);
            
            OrderItem::whereNull('order_id')->update([
                'order_id' => $order->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_number' => $orderNumber,
                'total' => $total,
                'units' => $units,
                'skus' => $skus,
                'items_count' => count($items),
                'timestamp' => now()->toISOString()
            ]);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Checkout failed: ' . $e->getMessage()
            ], 500);
        }
    }
}