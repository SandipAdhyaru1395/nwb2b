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
            // Validate the request (server will derive items/totals from cart)
            $request->validate([
                'branch_id' => 'required|integer|exists:branches,id',
                'delivery_method_id' => 'nullable|integer',
                'delivery_note' => 'nullable|string',
            ]);

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
            
            // Build order from server cart (authoritative)
            $cart = \App\Models\Cart::where('customer_id', optional($customer)->id)->first();
            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty.',
                ], 200);
            }
            // Ensure cart totals reflect latest product prices (recompute inline)
            $cartItemsAll = \App\Models\CartItem::where('cart_id', $cart->id)->get();
            $subtotalRecalc = 0.0;
            $unitsRecalc = 0;
            foreach ($cartItemsAll as $ci0) {
                $p0 = Product::find($ci0->product_id);
                $unit0 = $p0 ? (float)$p0->price : (float)$ci0->unit_price;
                $qty0 = (int)$ci0->quantity;
                $line0 = $unit0 * $qty0;
                if ((float)$ci0->unit_price !== $unit0 || (float)$ci0->line_total !== $line0) {
                    $ci0->unit_price = $unit0;
                    $ci0->line_total = $line0;
                    $ci0->save();
                }
                $subtotalRecalc += $line0;
                $unitsRecalc += $qty0;
            }
            $skusRecalc = (int) $cartItemsAll->count();
            \App\Models\Cart::where('id', $cart->id)->update([
                'subtotal' => $subtotalRecalc,
                'total_discount' => 0,
                'total' => $subtotalRecalc,
                'units' => $unitsRecalc,
                'skus' => $skusRecalc,
            ]);
            $cart->refresh();
            $cartItems = \App\Models\CartItem::where('cart_id', $cart->id)->get();
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty.',
                ], 200);
            }

            $total = 0;
            $units = 0;
            $skus = 0;
            $items = [];
            $totalWalletCreditEarned = 0;
            $pendingOrderItems = [];

            $adjustments = [];
            $adjusted = false;
            foreach ($cartItems as $ci) {
                $product = Product::find($ci->product_id);
                if (!$product) { continue; }
                $qty = (int) $ci->quantity;
                // Stock availability check at checkout time - reconcile cart quantities
                if (isset($product->stock_quantity) && $product->stock_quantity !== null) {
                    $available = (int) $product->stock_quantity;
                    if ($qty > $available) {
                        $adjusted = true;
                        $adjustments[] = [
                            'product_id' => (int) $product->id,
                            'product_name' => (string) $product->name,
                            'old_quantity' => $qty,
                            'new_quantity' => max(0, $available),
                        ];
                        if ($available <= 0) {
                            \App\Models\CartItem::where('id', $ci->id)->delete();
                        } else {
                            $ci->quantity = $available;
                            $ci->line_total = ((float)$product->price) * $available;
                            $ci->save();
                        }
                        // Do not add to pending order items now; we'll early return after reconciliation
                        continue;
                    }
                }

                $price = (float) $product->price;
                $lineTotal = $price * $qty;
                $pendingOrderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'wallet_credit_earned' => (float)($product->wallet_credit ?? 0) * $qty,
                    'total_price' => ($product->discounted_price != 0) ? $product->discounted_price * $qty : $lineTotal,
                ];

                $total += $lineTotal;
                $units += $qty;
                $skus += 1;
                $totalWalletCreditEarned += (float)($product->wallet_credit ?? 0) * $qty;
                $items[] = ['product_id' => (int)$product->id, 'quantity' => $qty];
            }

			// Auto-apply available wallet credit to this purchase (partial or full)
			$availableCredit = (float) optional($customer)->credit_balance ?? 0.0;
			$subtotal = $total;
			// Calculate total VAT from products in the cart
            $vatAmount = 0;
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $qty = (int)($item['quantity'] ?? 0);
                if ($product && isset($product->vat_amount)) {
                    $vatAmount += $product->vat_amount * $qty;
                }
            }
            // Only trust delivery_method_id from user, fetch method in secure server-side
            $deliveryMethod = $request->input('delivery_method_id') ? 
                \App\Models\DeliveryMethod::find($request->input('delivery_method_id')) : null;

            $deliveryCharge = $deliveryMethod ? (float)$deliveryMethod->rate : 0;
            $totalAmount = $subtotal + $vatAmount + $deliveryCharge;
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
                'delivery_method_id' => $deliveryMethod?->id,
                'delivery_method_name' => $deliveryMethod?->name,
                'delivery_time' => $deliveryMethod?->time,
                'delivery_charge' => $deliveryCharge,
                'delivery_note' => $request->input('delivery_note'),
            ]);
            
            if ($adjusted) {
                // Recompute cart totals and return adjustments response
                $cartItemsAll = \App\Models\CartItem::where('cart_id', $cart->id)->get();
                $subtotalRecalc = 0.0;
                $unitsRecalc = 0;
                foreach ($cartItemsAll as $ci0) {
                    $p0 = Product::find($ci0->product_id);
                    $unit0 = $p0 ? (float)$p0->price : (float)$ci0->unit_price;
                    $qty0 = (int)$ci0->quantity;
                    $line0 = $unit0 * $qty0;
                    if ((float)$ci0->unit_price !== $unit0 || (float)$ci0->line_total !== $line0) {
                        $ci0->unit_price = $unit0;
                        $ci0->line_total = $line0;
                        $ci0->save();
                    }
                    $subtotalRecalc += $line0;
                    $unitsRecalc += $qty0;
                }
                $skusRecalc = (int) $cartItemsAll->count();
                \App\Models\Cart::where('id', $cart->id)->update([
                    'subtotal' => $subtotalRecalc,
                    'total_discount' => 0,
                    'total' => $subtotalRecalc,
                    'units' => $unitsRecalc,
                    'skus' => $skusRecalc,
                ]);
                return response()->json([
                    'success' => false,
                    'code' => 'stock_adjusted',
                    'message' => 'Some items were adjusted to available stock. Please review your basket.',
                    'adjustments' => $adjustments,
                ], 200);
            }

            foreach ($pendingOrderItems as $poi) {
                $poi['order_id'] = $order->id;
                OrderItem::create($poi);
            }

            // Reduce product stock quantities after successful order item creation
            foreach ($pendingOrderItems as $poi) {
                $p = Product::find($poi['product_id']);
                if ($p && isset($p->stock_quantity) && $p->stock_quantity !== null) {
                    $newStock = max(0, (int) $p->stock_quantity - (int) ($poi['quantity'] ?? 0));
                    if ($newStock !== (int)$p->stock_quantity) {
                        $p->stock_quantity = $newStock;
                        $p->save();
                    }
                }
            }

            // Clear the cart after successful checkout
            \App\Models\CartItem::where('cart_id', $cart->id)->delete();
            \App\Models\Cart::where('id', $cart->id)->update([
                'subtotal' => 0,
                'total_discount' => 0,
                'total' => 0,
                'units' => 0,
                'skus' => 0,
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
                    'vat_amount' => isset($product->vat_amount) ? (float)$product->vat_amount : 0,
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