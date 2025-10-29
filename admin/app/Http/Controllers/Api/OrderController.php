<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Address;
use App\Models\WalletTransaction;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    
    public function index(Request $request){
        
        $setting = Helpers::setting();

        $limit = (int)($request->query('limit', 10));
        $totalCount = Order::count();
        $orders = Order::
            latest('created_at')
            ->where('customer_id', $request->user()->id)
            ->take($limit)
            ->get();
        
        $data = $orders->map(function(Order $order)use($setting){
            return [
                'order_number' => $order->order_number,
                'ordered_at' => optional($order->created_at)->format('H:i d/m/Y'),
                'payment_status' => strtoupper($order->payment_status),
                'fulfillment_status' => strtoupper($order->status),
                'units' => (int)($order->units_count ?? 0),
                'skus' => (int)($order->skus_count ?? 0),
                'currency_symbol' => $setting['currency_symbol'] ?? '',
                'total_paid' => (float)($order->total_amount ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'orders' => $data,
            'has_more' => $totalCount > $limit,
            'total' => $totalCount,
        ]);
    }

    public function show(Request $request, string $orderNumber)
    {
        $setting = Helpers::setting();
        $order = Order::with(['items', 'customer'])
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $items = $order->items()->with('product')->get()->map(function(OrderItem $item) {
            $image = optional($item->product)->image_url;
            if ($image && !Str::startsWith($image, ['http://', 'https://', '/'])) {
                $image = url($image);
            }
            return [
                'product_id' => (int) $item->product_id,
                'product_name' => optional($item->product)->name,
                'product_image' => $image,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'wallet_credit_earned' => (float) ($item->wallet_credit_earned ?? 0),
                'total_price' => (float) $item->total_price,
            ];
        });

        return response()->json([
            'success' => true,
            'order' => [
                'order_number' => $order->order_number,
                'ordered_at' => optional($order->created_at)->format('H:i d/m/Y'),
                'payment_status' => strtoupper($order->payment_status),
                'fulfillment_status' => strtoupper($order->status),
                'units' => (int)($order->units_count ?? 0),
                'skus' => (int)($order->skus_count ?? 0),
                'subtotal' => (float)($order->subtotal ?? 0),
                'vat_amount' => (float)($order->vat_amount ?? 0),
                'delivery' => 'FREE',
                'wallet_discount' => (float)($order->wallet_credit_used ?? 0) * -1,
                'total_paid' => (float)($order->total_amount ?? 0),
                'payment_amount' => (float)($order->payment_amount ?? max(0, ($order->total_amount ?? 0) - ($order->wallet_credit_used ?? 0))),
                'currency_symbol' => $setting['currency_symbol'] ?? '',
                'address' => [
                    'line1' => $order->address_line1,
                    'line2' => $order->address_line2,
                    'city' => $order->city,
                    'zip' => $order->zip_code,
                    'country' => $order->country,
                ],
                'items' => $items,
            ],
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
                'skus' => 'required|integer|min:1',
                'branch_id' => 'required|integer|exists:branches,id',
            ]);
    
            $items = $request->input('items');
            $total = $request->input('total');
            $units = $request->input('units');
            $skus = $request->input('skus');
            $branchId = (int) $request->input('branch_id');
            $customer = $request->user();

            // Validate that the address belongs to the authenticated user
            $branch = Branch::where('id', $branchId)
                ->where('customer_id', optional($customer)->id)
                ->first();

            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid branch selected.',
                    'errors' => [
                        'branch_id' => ['The selected branch is invalid.']
                    ]
                ], 200);
            }
            
            $total = 0;
            
            OrderItem::whereNull('order_id')->delete();
            
            $totalWalletCreditEarned = 0;
            
            foreach($items as $item){

                $product = Product::find($item['product_id']);
                
                if(!$product){
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found',
                    ], 200);
                }
                // Enforce step quantity multiples at API level
                $step = (int)($product->step_quantity ?? 1);
                $qty = (int)($item['quantity'] ?? 0);
                if ($step > 1 && ($qty % $step) !== 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Quantity for '{$product->name}' must be in multiples of {$step}.",
                        'errors' => [
                            'items' => ["Quantity must be a multiple of {$step} for product {$product->name}."]
                        ]
                    ], 200);
                }
                

                OrderItem::create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'wallet_credit_earned' => (float)($product->wallet_credit ?? 0) * $qty,
                    'total_price' => ($product->discounted_price != 0) ? $product->discounted_price * $qty : $product->price * $qty,
                ]);
                
                $total += $product->price * $qty;
                $totalWalletCreditEarned += (float)($product->wallet_credit ?? 0) * $qty;
            }
           
			if($total != $request->input('total')){
                return response()->json([
                    'success' => false,
                    'message' => 'Prices have been updated. Update your cart and try again.',
                ], 200);
            }

			// Auto-apply available wallet credit to this purchase (partial or full)
			$availableCredit = (float) optional($customer)->credit_balance ?? 0.0;
			$subtotal = $total;
			$vatAmount = 0; // extend later if VAT is introduced
			$totalAmount = $subtotal + $vatAmount; // subtotal + vat = total_amount
			$walletCreditUsed = min($totalAmount, $availableCredit);
			$outstandingAmount = $totalAmount - $walletCreditUsed; // total - wallet_used = outstanding

            $orderNumber = 'ORD-' . strtoupper(uniqid());
                
            $order = Order::create([
                'order_number' => $orderNumber,
                'order_date' => now(),
				'customer_id' => optional($customer)->id ?? null,
				'subtotal' => $subtotal,
				'vat_amount' => $vatAmount,
				'total_amount' => $totalAmount,
                'payment_amount' => max(0, $totalAmount - $walletCreditUsed),
				'wallet_credit_used' => $walletCreditUsed,
                'units_count' => $units,
                'skus_count' => $skus,
                'items_count' => count($items),
                'payment_terms' => 'net_30',
				'payment_status' => ($outstandingAmount <= 0 ? 'Paid' : 'Unpaid'),
				'outstanding_amount' => $outstandingAmount,
                'estimated_delivery_date' => now()->addDays(7),
                'status' => 'New',
                // Persist delivery address snapshot on the order
                'branch_name' => (string) $branch->name,
                'country' => (string) $branch->country,
                'address_line1' => (string) $branch->address_line1,
                'address_line2' => (string) ($branch->address_line2 ?? ''),
                'city' => (string) $branch->city,
                'zip_code' => (string) $branch->zip_code,
            ]);
            
            OrderItem::whereNull('order_id')->update([
                'order_id' => $order->id
            ]);
            
			// Wallet credit debit (applied to this order) and earning credit
			if ($customer) {
				if ($walletCreditUsed > 0) {
					$customer->credit_balance = (float)($customer->credit_balance ?? 0) - $walletCreditUsed;
					$customer->save();
					WalletTransaction::create([
						'customer_id' => $customer->id,
						'order_id' => $order->id,
						'amount' => $walletCreditUsed,
						'type' => 'debit',
						'description' => 'Wallet credit applied to order',
						'balance_after' => $customer->credit_balance,
					]);
				}
				$customer->credit_balance = (float)($customer->credit_balance ?? 0) + $totalWalletCreditEarned;
				$customer->save();
				WalletTransaction::create([
					'customer_id' => $customer->id,
					'order_id' => $order->id,
					'amount' => $totalWalletCreditEarned,
					'type' => 'credit',
					'description' => 'Wallet credit earned on order',
					'balance_after' => $customer->credit_balance,
				]);
			}

			return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_number' => $orderNumber,
                'total' => $total,
                'units' => $units,
				'skus' => $skus,
                'items_count' => count($items),
				'wallet_credit_earned' => $totalWalletCreditEarned,
				'wallet_credit_used' => $walletCreditUsed,
				'total_amount' => $totalAmount,
				'outstanding_amount' => $outstandingAmount,
				'wallet_balance' => (float) optional($customer)->credit_balance ?? 0.0,
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

    /**
     * Reorder: given an existing order number, return a normalized items payload
     * suitable for the frontend cart and checkout calculations.
     */
    public function reorder(Request $request, string $orderNumber)
    {
        $order = Order::with(['items', 'items.product'])
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $customer = $request->user();
        if ($customer && $order->customer_id && (int)$order->customer_id !== (int)$customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reorder this order',
            ], 403);
        }

        $items = [];
        $subtotal = 0.0;
        $units = 0;
        $skus = 0;

        foreach (($order->items ?? []) as $item) {
            $product = $item->product ?: Product::find($item->product_id);
            if (!$product || (int)($product->is_active ?? 1) !== 1) {
                // skip inactive/missing products
                continue;
            }
            $qty = (int) $item->quantity;
            // Enforce step multiples by rounding up to nearest valid multiple
            $step = (int)($product->step_quantity ?? 1);
            if ($step > 1 && $qty > 0) {
                $remainder = $qty % $step;
                if ($remainder !== 0) {
                    $qty = $qty + ($step - $remainder);
                }
            }
            if ($qty <= 0) { continue; }

            $price = (float) $product->price;
            $subtotal += $price * $qty;
            $units += $qty;
            $skus += 1;

            $items[] = [
                'product_id' => (int) $product->id,
                'quantity' => $qty,
                'product' => [
                    'id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'image' => $product->image_url ? (str_starts_with($product->image_url, 'http') ? $product->image_url : asset($product->image_url)) : null,
                    'step_quantity' => (int)($product->step_quantity ?? 1),
                    'price' => (string) (Helpers::setting()['currency_symbol'] ?? '£') . number_format($price, 2),
                    'discount' => null,
                    'wallet_credit' => isset($product->wallet_credit) ? (float)$product->wallet_credit : 0,
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'items' => $items,
            'units' => (int) $units,
            'skus' => (int) $skus,
            'subtotal' => (float) $subtotal,
            'total' => (float) $subtotal, // discount handled on frontend if applicable
        ]);
    }
}