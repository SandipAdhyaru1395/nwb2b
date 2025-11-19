<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Branch;
use App\Models\WalletTransaction;
use App\Models\OrderRef;
use App\Services\WarehouseProductSyncService;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrderController extends Controller
{
  public function index()
  {
    return view('content.order.list');
  }

  public function add()
  {
    $customers = \App\Models\Customer::where('is_active', 1)
      ->orderBy('company_name')
      ->orderBy('email')
      ->get();
    $products = Product::where('is_active', 1)->select('id', 'name', 'sku', 'price', 'wallet_credit', 'image_url')->get();

    return view('content.order.add', [
      'customers' => $customers,
      'products' => $products
    ]);
  }

  public function create(Request $request)
  {
    // Main form create (from add page)
    $validated = $request->validate([
      'customer_id' => ['required', 'integer', 'exists:customers,id'],
      'date' => [
        'required',
        function ($attribute, $value, $fail) {
          if (empty($value)) {
            $fail('Date is required');
            return;
          }
          // Try to parse d/m/Y H:i format (e.g., "12/11/2025 12:06")
          if (strpos($value, '/') !== false) {
            try {
              $parsed = Carbon::createFromFormat('d/m/Y H:i', $value);
              if ($parsed === false) {
                $fail('Date must be in dd/mm/yyyy hh:mm format');
              }
            } catch (\Exception $e) {
              try {
                $parsed = Carbon::createFromFormat('d/m/Y', $value);
                if ($parsed === false) {
                  $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
                }
              } catch (\Exception $e2) {
                $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
              }
            }
          } else {
            // Try standard date formats
            try {
              $parsed = Carbon::parse($value);
              if ($parsed === false) {
                $fail('Date must be a valid date');
              }
            } catch (\Exception $e) {
              $fail('Date must be a valid date');
            }
          }
        }
      ],
      'shipping_charge' => ['nullable', 'numeric', 'min:0'],
      'delivery_note' => ['nullable', 'string'],
      'address_id' => ['required', 'integer', 'exists:branches,id'],
      'products' => ['required', 'array', 'min:1'],
      'products.*.product_id' => ['required', 'exists:products,id'],
      'products.*.quantity' => ['required', 'numeric', 'min:1'],
      'products.*.unit_cost' => ['required', 'numeric', 'min:0'],
    ], [
      'customer_id.required' => 'Customer is required',
      'customer_id.integer' => 'Customer must be a valid selection',
      'customer_id.exists' => 'Selected customer does not exist',
      'date.required' => 'Date is required',
      'address_id.required' => 'Address is required',
      'address_id.integer' => 'Address must be a valid selection',
      'address_id.exists' => 'Selected address does not exist',
      'products.required' => 'At least one product is required',
      'products.min' => 'At least one product is required',
      'products.*.product_id.required' => 'Product is required',
      'products.*.product_id.exists' => 'Selected product does not exist',
      'products.*.quantity.required' => 'Quantity is required',
      'products.*.quantity.numeric' => 'Quantity must be a number',
      'products.*.quantity.min' => 'Quantity must be at least 1',
      'products.*.unit_cost.required' => 'Sale price is required',
      'products.*.unit_cost.numeric' => 'Sale price must be a number',
      'products.*.unit_cost.min' => 'Sale price must be 0 or greater',
      'shipping_charge.numeric' => 'Shipping charge must be a number',
      'shipping_charge.min' => 'Shipping charge must be 0 or greater',
    ]);

    // Parse date (support d/m/Y H:i and d/m/Y)
    $date = $validated['date'];
    if (strpos($date, '/') !== false) {
      try {
        $date = Carbon::createFromFormat('d/m/Y H:i', $date);
      } catch (\Exception $e) {
        $date = Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
      }
    } else {
      $date = Carbon::parse($date);
    }

    // Normalize products array (in case keys are product IDs)
    $products = array_filter($validated['products'], function($productData) {
      return isset($productData['product_id']);
    });

    // Validate product stock availability
    $stockErrors = [];
    foreach ($products as $index => $productData) {
      $productId = (int) ($productData['product_id'] ?? 0);
      $orderedQuantity = (float) ($productData['quantity'] ?? 0);
      
      if ($productId > 0 && $orderedQuantity > 0) {
        $product = Product::find($productId);
        if ($product) {
          $availableQuantity = (float) ($product->stock_quantity ?? 0);
          if ($orderedQuantity > $availableQuantity) {
            $productName = $product->name ?? 'Product #' . $productId;
            $stockErrors["products.{$index}.quantity"] = "Insufficient stock for {$productName}. Available: {$availableQuantity}, Requested: {$orderedQuantity}";
          }
        }
      }
    }
    
    if (!empty($stockErrors)) {
      return redirect()->back()
        ->withErrors($stockErrors)
        ->withInput();
    }

    // Get branch/address if address_id is provided
    $branch = null;
    $addressData = [];
    if (!empty($validated['address_id'])) {
      $branch = Branch::find($validated['address_id']);
      if ($branch) {
        $addressData = [
          'branch_name' => $branch->name,
          'address_line1' => $branch->address_line1,
          'address_line2' => $branch->address_line2,
          'city' => $branch->city,
          'zip_code' => $branch->zip_code,
          'country' => $branch->country,
        ];
      }
    }

    // Get customer for wallet credit calculations
    $customer = \App\Models\Customer::findOrFail($validated['customer_id']);

    // Get order number from order_ref table (so column) - always use so
    $orderRef = OrderRef::orderBy('id', 'desc')->first();
    
    if (!$orderRef) {
      // Create initial order_ref record if it doesn't exist
      $orderRef = OrderRef::create([
        'so' => 1,
        'qa' => 1,
        'po' => 1,
      ]);
    }
    
    // Always use order_ref.so for order number
    $orderNumber = $orderRef->so ?? 1;
    
    // Increment so for next order
    $orderRef->update([
      'so' => ($orderRef->so ?? 0) + 1,
    ]);

    // Process create in transaction
    $order = DB::transaction(function () use ($validated, $products, $date, $addressData, $customer, $orderNumber) {

      // Create order
      $orderData = [
        'customer_id' => $validated['customer_id'],
        'order_date' => $date,
        'order_number' => $orderNumber,
        'delivery_charge' => $validated['shipping_charge'] ?? 0,
        'delivery_note' => $validated['delivery_note'] ?? null,
        'status' => 'Completed',
        'payment_status' => 'Due',
        'subtotal' => 0,
        'vat_amount' => 0,
        'total_amount' => 0,
        'paid_amount' => 0,
        'unpaid_amount' => 0,
        'outstanding_amount' => 0,
        'items_count' => 0,
        'units_count' => 0,
        'skus_count' => 0,
        'wallet_credit_used' => 0,
      ];

      // Add address data if branch was selected
      if (!empty($addressData)) {
        $orderData = array_merge($orderData, $addressData);
      }

      $order = Order::create($orderData);

      // Create order items and calculate wallet credit earned
      $walletCreditEarned = 0;
      foreach ($products as $productData) {
        $quantity = (int) $productData['quantity'];
        $unitPrice = (float) ($productData['unit_cost'] ?? 0);
        
        // Get fresh product data to ensure we have the latest wallet_credit value
        $product = Product::findOrFail($productData['product_id']);
        
        // Calculate wallet credit earned per item: product wallet_credit * quantity
        $walletCreditEarnedPerItem = round((float)($product->wallet_credit ?? 0) * $quantity, 2);
        $walletCreditEarned += $walletCreditEarnedPerItem;
        
        // Calculate total price
        $totalPrice = round($unitPrice * $quantity, 2);

        // Create order item
        OrderItem::create([
          'order_id' => $order->id,
          'product_id' => $productData['product_id'],
          'quantity' => $quantity,
          'unit_price' => $unitPrice,
          'wallet_credit_earned' => $walletCreditEarnedPerItem,
          'total_price' => $totalPrice,
        ]);
      }

      // Refresh order to get new items
      $order->refresh();
      $order->load('items');

      // Calculate subtotal
      $subtotal = $order->items->sum('total_price');

      // Calculate wallet_credit_used based on subtotal and available credit
      $currentBalance = (float)($customer->credit_balance ?? 0);
      $walletCreditUsed = min($subtotal, $currentBalance);

      // Update customer credit balance
      if ($walletCreditUsed > 0) {
        $customer->credit_balance = $currentBalance - $walletCreditUsed;
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

      // Apply wallet credit earned
      if ($walletCreditEarned > 0) {
        $customer->credit_balance = ($customer->credit_balance ?? 0) + $walletCreditEarned;
        $customer->save();
        
        WalletTransaction::create([
          'customer_id' => $customer->id,
          'order_id' => $order->id,
          'amount' => $walletCreditEarned,
          'type' => 'credit',
          'description' => 'Wallet credit earned on order',
          'balance_after' => $customer->credit_balance,
        ]);
      }

      // Update order with wallet_credit_used
      $order->wallet_credit_used = $walletCreditUsed;
      $order->save();

      // Update order totals
      $this->updateOrderTotals($order->id);
      
      // Decrement product quantities after order is created
      foreach ($products as $productData) {
        $quantity = (float) ($productData['quantity'] ?? 0);
        $productId = (int) ($productData['product_id'] ?? 0);
        if ($productId > 0 && $quantity > 0) {
          try {
            WarehouseProductSyncService::adjustQuantity($productId, 'subtraction', $quantity);
          } catch (\Exception $e) {
            // Log error but don't fail the transaction
            \Log::error("Failed to adjust product quantity for product {$productId}: " . $e->getMessage());
          }
        }
      }
      
      return $order;
    });

    Toastr::success('Order created successfully!');
    return redirect()->route('order.list');
  }

  public function edit($id)
  {
    $order = Order::with(['customer.branches', 'items.product', 'statusHistories'])->findOrFail($id);
    $products = Product::where('is_active', 1)->select('id', 'name', 'sku', 'price', 'wallet_credit', 'image_url')->get();

    return view('content.order.edit', [
      'order' => $order,
      'products' => $products
    ]);
  }

  public function itemsAjax(Request $request)
  {
    $orderId = $request->get('id');
    $query = Order::where('orders.id', $orderId)
      ->join('order_items', 'order_items.order_id', '=', 'orders.id')
      ->join('products', 'products.id', '=', 'order_items.product_id')
      ->whereNull('orders.deleted_at')
      ->whereNull('order_items.deleted_at')
      ->whereNull('products.deleted_at')
      ->select([
        'order_items.id',
        'order_items.product_id',
        'products.name as product_name',
        'products.image_url',
        'order_items.quantity',
        'order_items.unit_price',
        'order_items.wallet_credit_earned'
      ]);

    return DataTables::of($query)
      ->toJson();
  }

  public function update(Request $request)
  {


    // Main form update (from details page)
    $validated = $request->validate([
      'id' => ['required', 'exists:orders,id'],
      'date' => [
        'required',
        function ($attribute, $value, $fail) {
          if (empty($value)) {
            $fail('Date is required');
            return;
          }
          // Try to parse d/m/Y H:i format (e.g., "12/11/2025 12:06")
          if (strpos($value, '/') !== false) {
            try {
              $parsed = Carbon::createFromFormat('d/m/Y H:i', $value);
              if ($parsed === false) {
                $fail('Date must be in dd/mm/yyyy hh:mm format');
              }
            } catch (\Exception $e) {
              try {
                $parsed = Carbon::createFromFormat('d/m/Y', $value);
                if ($parsed === false) {
                  $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
                }
              } catch (\Exception $e2) {
                $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
              }
            }
          } else {
            // Try standard date formats
            try {
              $parsed = Carbon::parse($value);
              if ($parsed === false) {
                $fail('Date must be a valid date');
              }
            } catch (\Exception $e) {
              $fail('Date must be a valid date');
            }
          }
        }
      ],
      'shipping_charge' => ['nullable', 'numeric', 'min:0'],
      'delivery_note' => ['nullable', 'string'],
      'address_id' => ['required', 'integer', 'exists:branches,id'],
      'products' => ['required', 'array', 'min:1'],
      'products.*.product_id' => ['required', 'exists:products,id'],
      'products.*.quantity' => ['required', 'numeric', 'min:1'],
      'products.*.unit_cost' => ['required', 'numeric', 'min:0'],
    ], [
      'id.required' => 'Order ID is required',
      'id.exists' => 'Order not found',
      'date.required' => 'Date is required',
      'address_id.required' => 'Address is required',
      'address_id.integer' => 'Address must be a valid selection',
      'address_id.exists' => 'Selected address does not exist',
      'products.required' => 'At least one product is required',
      'products.min' => 'At least one product is required',
      'products.*.product_id.required' => 'Product is required',
      'products.*.product_id.exists' => 'Selected product does not exist',
      'products.*.quantity.required' => 'Quantity is required',
      'products.*.quantity.numeric' => 'Quantity must be a number',
      'products.*.quantity.min' => 'Quantity must be at least 1',
      'products.*.unit_cost.required' => 'Sale price is required',
      'products.*.unit_cost.numeric' => 'Sale price must be a number',
      'products.*.unit_cost.min' => 'Sale price must be 0 or greater',
      'shipping_charge.numeric' => 'Shipping charge must be a number',
      'shipping_charge.min' => 'Shipping charge must be 0 or greater',
    ]);

    $order = Order::with('items')->findOrFail($validated['id']);

    // Parse date (support d/m/Y H:i and d/m/Y)
    $date = $validated['date'];
    if (strpos($date, '/') !== false) {
      try {
        $date = Carbon::createFromFormat('d/m/Y H:i', $date);
      } catch (\Exception $e) {
        $date = Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
      }
    } else {
      $date = Carbon::parse($date);
    }

    // Normalize products array (in case keys are product IDs)
    $products = array_filter($validated['products'], function($productData) {
      return isset($productData['product_id']);
    });

    // Validate product stock availability (accounting for quantities that will be restored)
    // Build a map of old quantities by product_id
    $oldQuantitiesByProduct = [];
    foreach ($order->items as $oldItem) {
      $oldProductId = (int) ($oldItem->product_id ?? 0);
      $oldQuantity = (float) ($oldItem->quantity ?? 0);
      if ($oldProductId > 0) {
        $oldQuantitiesByProduct[$oldProductId] = ($oldQuantitiesByProduct[$oldProductId] ?? 0) + $oldQuantity;
      }
    }

    $stockErrors = [];
    foreach ($products as $index => $productData) {
      $productId = (int) ($productData['product_id'] ?? 0);
      $orderedQuantity = (float) ($productData['quantity'] ?? 0);
      
      if ($productId > 0 && $orderedQuantity > 0) {
        $product = Product::find($productId);
        if ($product) {
          $currentStock = (float) ($product->stock_quantity ?? 0);
          // Add back the old quantity that will be restored
          $oldQuantity = (float) ($oldQuantitiesByProduct[$productId] ?? 0);
          $availableQuantity = $currentStock + $oldQuantity;
          
          if ($orderedQuantity > $availableQuantity) {
            $productName = $product->name ?? 'Product #' . $productId;
            $stockErrors["products.{$index}.quantity"] = "Insufficient stock for {$productName}. Available: {$availableQuantity}, Requested: {$orderedQuantity}";
          }
        }
      }
    }
    
    if (!empty($stockErrors)) {
      return redirect()->back()
        ->withErrors($stockErrors)
        ->withInput();
    }

    // Get branch/address if address_id is provided
    $branch = null;
    $addressData = [];
    if (!empty($validated['address_id'])) {
      $branch = Branch::find($validated['address_id']);
      if ($branch) {
        $addressData = [
          'branch_name' => $branch->name,
          'address_line1' => $branch->address_line1,
          'address_line2' => $branch->address_line2,
          'city' => $branch->city,
          'zip_code' => $branch->zip_code,
          'country' => $branch->country,
        ];
      }
    }

    // Process update in transaction
    DB::transaction(function () use ($order, $products, $validated, $date, $addressData) {
      // Calculate old wallet credit earned before deleting items
      $oldWalletCreditEarned = $order->items->sum('wallet_credit_earned');
      $oldWalletCreditUsed = (float)($order->wallet_credit_used ?? 0);
      
      // Get customer for wallet credit adjustments
      $customer = $order->customer;

      // Restore old product quantities before deleting items
      foreach ($order->items as $oldItem) {
        $oldQuantity = (float) ($oldItem->quantity ?? 0);
        $oldProductId = (int) ($oldItem->product_id ?? 0);
        if ($oldProductId > 0 && $oldQuantity > 0) {
          try {
            WarehouseProductSyncService::adjustQuantity($oldProductId, 'addition', $oldQuantity);
          } catch (\Exception $e) {
            // Log error but don't fail the transaction
            \Log::error("Failed to restore product quantity for product {$oldProductId}: " . $e->getMessage());
          }
        }
      }

      // Delete old items
      $order->items()->delete();

      // Create new items and calculate new wallet credit earned
      // This handles: adding products, removing products, and changing quantities
      $newWalletCreditEarned = 0;
      foreach ($products as $productData) {
        $quantity = (int) $productData['quantity'];
        $unitPrice = (float) ($productData['unit_cost'] ?? 0); // Note: form uses unit_cost but it's actually unit_price for orders
        
        // Get fresh product data to ensure we have the latest wallet_credit value
        $product = Product::findOrFail($productData['product_id']);
        
        // Calculate wallet credit earned per item: product wallet_credit * quantity
        // This automatically recalculates when quantity changes
        $walletCreditEarned = round((float)($product->wallet_credit ?? 0) * $quantity, 2);
        $newWalletCreditEarned += $walletCreditEarned;
        
        // Calculate total price
        $totalPrice = round($unitPrice * $quantity, 2);

        // Create order item with recalculated wallet_credit_earned
        // This ensures order_items.wallet_credit_earned is always correct for the current quantity
        OrderItem::create([
          'order_id' => $order->id,
          'product_id' => $productData['product_id'],
          'quantity' => $quantity,
          'unit_price' => $unitPrice,
          'wallet_credit_earned' => $walletCreditEarned, // Recalculated based on current quantity
          'total_price' => $totalPrice,
        ]);
      }

      // Refresh order to get new items
      $order->refresh();
      $order->load('items');

      // Calculate new subtotal
      $newSubtotal = $order->items->sum('total_price');

      // Update order totals first to get new total_amount
      $this->updateOrderTotals($order->id);
      $order->refresh();

      // Recalculate wallet_credit_used based on new subtotal
      // Note: wallet_credit_earned = credit customer earns (added to balance)
      //       wallet_credit_used = credit customer uses (subtracted from balance)
      $newWalletCreditUsed = 0;
      if ($customer) {
        // Reload customer to get fresh balance data
        $customer->refresh();
        $currentBalance = (float)($customer->credit_balance ?? 0);
        
        // To calculate available credit for new order, we need to:
        // 1. Add back the old wallet_credit_used (it was subtracted before)
        // 2. Subtract the old wallet_credit_earned (it was added before)
        // This gives us the balance as if the old order never happened
        $balanceBeforeOldOrder = $currentBalance - $oldWalletCreditEarned + $oldWalletCreditUsed;
        
        // Calculate new wallet_credit_used based on new subtotal and available credit
        $newWalletCreditUsed = min($newSubtotal, $balanceBeforeOldOrder);

        // Adjust customer credit balance step by step
        // Step 1: Reverse old wallet_credit_earned (subtract - it was added to balance)
        if ($oldWalletCreditEarned > 0) {
          $customer->credit_balance = $currentBalance - $oldWalletCreditEarned;
          $customer->save();
          $currentBalance = $customer->credit_balance;
          
          WalletTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => $oldWalletCreditEarned,
            'type' => 'debit',
            'description' => 'Wallet credit earned reversed due to order update',
            'balance_after' => $currentBalance,
          ]);
        }

        // Step 2: Reverse old wallet_credit_used (add back - it was subtracted from balance)
        if ($oldWalletCreditUsed > 0) {
          $customer->credit_balance = $currentBalance + $oldWalletCreditUsed;
          $customer->save();
          $currentBalance = $customer->credit_balance;
          
          WalletTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => $oldWalletCreditUsed,
            'type' => 'credit',
            'description' => 'Wallet credit used reversed due to order update',
            'balance_after' => $currentBalance,
          ]);
        }

        // Step 3: Apply new wallet_credit_used (subtract from balance)
        if ($newWalletCreditUsed > 0) {
          $customer->credit_balance = $currentBalance - $newWalletCreditUsed;
          $customer->save();
          $currentBalance = $customer->credit_balance;
          
          WalletTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => $newWalletCreditUsed,
            'type' => 'debit',
            'description' => 'Wallet credit applied to updated order',
            'balance_after' => $currentBalance,
          ]);
        }

        // Step 4: Apply new wallet_credit_earned (add to balance)
        if ($newWalletCreditEarned > 0) {
          $customer->credit_balance = $currentBalance + $newWalletCreditEarned;
          $customer->save();
          
          WalletTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => $newWalletCreditEarned,
            'type' => 'credit',
            'description' => 'Wallet credit earned on updated order',
            'balance_after' => $customer->credit_balance,
          ]);
        }
      }

      // Update order with new wallet_credit_used
      // Note: order_number (reference_no) is not updated - keep original value
      $updateData = [
        'order_date' => $date,
        'order_number' => $order->order_number, // Keep original reference number, don't update
        'delivery_charge' => $validated['shipping_charge'] ?? 0,
        'delivery_note' => $validated['delivery_note'] ?? null,
        'wallet_credit_used' => $newWalletCreditUsed,
      ];

      // Add address data if branch was selected
      if (!empty($addressData)) {
        $updateData = array_merge($updateData, $addressData);
      }

      $order->update($updateData);

      // Update order totals again to recalculate with new wallet_credit_used
      $this->updateOrderTotals($order->id);
      
      // Decrement new product quantities after order is updated
      foreach ($products as $productData) {
        $quantity = (float) ($productData['quantity'] ?? 0);
        $productId = (int) ($productData['product_id'] ?? 0);
        if ($productId > 0 && $quantity > 0) {
          try {
            WarehouseProductSyncService::adjustQuantity($productId, 'subtraction', $quantity);
          } catch (\Exception $e) {
            // Log error but don't fail the transaction
            \Log::error("Failed to adjust product quantity for product {$productId}: " . $e->getMessage());
          }
        }
      }
    });

    Toastr::success('Order updated successfully!');
    return redirect()->route('order.list');
  }

  public function getCustomerBranches($customerId)
  {
    $customer = \App\Models\Customer::with('branches')->findOrFail($customerId);
    $branches = $customer->branches->map(function($branch) {
      $addressText = $branch->name . ' - ' . $branch->address_line1;
      if ($branch->address_line2) {
        $addressText .= ', ' . $branch->address_line2;
      }
      $addressText .= ', ' . $branch->city;
      if ($branch->zip_code) {
        $addressText .= ' ' . $branch->zip_code;
      }
      if ($branch->country) {
        $addressText .= ', ' . $branch->country;
      }
      return [
        'id' => $branch->id,
        'text' => $addressText
      ];
    });
    
    return response()->json($branches);
  }

  public function ajaxList(Request $request)
  {
    $query = Order::select([
      'orders.id',
      'orders.order_number',
      'orders.order_date',
      'orders.total_amount',
      'orders.paid_amount',
      'orders.unpaid_amount',
      'orders.vat_amount',
      'orders.payment_status',
      'orders.status as order_status',
      'customers.email as customer_email',
      'customers.company_name as customer_name'
    ])->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
      ->whereNull('orders.deleted_at')
      ->orderBy('orders.id', 'desc');

    return DataTables::eloquent($query)
      ->filterColumn('order_number', function ($query, $keyword) {
        $query->where('orders.order_number', 'like', "%{$keyword}%");
      })
      ->filterColumn('order_date', function ($query, $keyword) {
        $query->where('orders.order_date', 'like', "%{$keyword}%");
      })
      ->filterColumn('customer_name', function ($query, $keyword) {
        $query->where(function($q) use ($keyword) {
          $q->where('customers.company_name', 'like', "%{$keyword}%")
            ->orWhere('customers.email', 'like', "%{$keyword}%");
        });
      })
      ->filterColumn('total_amount', function ($query, $keyword) {
        $query->where('orders.total_amount', 'like', "%{$keyword}%");
      })
      ->filterColumn('paid_amount', function ($query, $keyword) {
        $query->where('orders.paid_amount', 'like', "%{$keyword}%");
      })
      ->filterColumn('unpaid_amount', function ($query, $keyword) {
        $query->where('orders.unpaid_amount', 'like', "%{$keyword}%");
      })
      ->filterColumn('vat_amount', function ($query, $keyword) {
        $query->where('orders.vat_amount', 'like', "%{$keyword}%");
      })
      ->filterColumn('order_status', function ($query, $keyword) {
        $query->where('orders.status', 'like', "%{$keyword}%");
      })
      ->filterColumn('payment_status', function ($query, $keyword) {
        $query->where('orders.payment_status', 'like', "%{$keyword}%");
      })
      ->orderColumn('order_number', function ($query, $order) {
        $query->orderBy('orders.order_number', $order);
      })
      ->orderColumn('order_date', function ($query, $order) {
        $query->orderBy('orders.order_date', $order);
      })
      ->orderColumn('total_amount', function ($query, $order) {
        $query->orderBy('orders.total_amount', $order);
      })
      ->orderColumn('paid_amount', function ($query, $order) {
        $query->orderBy('orders.paid_amount', $order);
      })
      ->orderColumn('unpaid_amount', function ($query, $order) {
        $query->orderBy('orders.unpaid_amount', $order);
      })
      ->orderColumn('vat_amount', function ($query, $order) {
        $query->orderBy('orders.vat_amount', $order);
      })
      ->orderColumn('order_status', function ($query, $order) {
        $query->orderBy('orders.status', $order);
      })
      ->orderColumn('payment_status', function ($query, $order) {
        $query->orderBy('orders.payment_status', $order);
      })
      ->toJson();
  }

  public function delete($id)
  {
    $order = Order::with('items')->findOrFail($id);
    // Optionally cascade delete items
    DB::transaction(function () use ($order) {
      // Restore product quantities before deleting items
      foreach ($order->items as $item) {
        $quantity = (float) ($item->quantity ?? 0);
        $productId = (int) ($item->product_id ?? 0);
        if ($productId > 0 && $quantity > 0) {
          try {
            WarehouseProductSyncService::adjustQuantity($productId, 'addition', $quantity);
          } catch (\Exception $e) {
            // Log error but don't fail the transaction
            \Log::error("Failed to restore product quantity for product {$productId}: " . $e->getMessage());
          }
        }
      }
      
      $order->items()->delete();
      $order->statusHistories()->delete();
      $order->delete();
    });
    Toastr::success('Order deleted successfully');
    return redirect()->back();
  }

  /**
   * Create a new order item
   */
  public function createItem(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'order_id' => ['required','integer','exists:orders,id'],
        'product_id' => ['required','integer','exists:products,id'],
        'quantity' => ['required','integer','min:1'],
        'unit_price' => ['required','numeric','min:0.01'],
      ]);

      if ($validator->fails()) {
        return back()
          ->withErrors($validator, 'addItemModal')
          ->withInput();
      }

      $validated = $validator->validated();

      $product = Product::findOrFail($validated['product_id']);
      
      // Calculate wallet credit earned
      $walletCreditEarned = $product->wallet_credit * $validated['quantity'];
      
      // Calculate total price
      $totalPrice = $validated['unit_price'] * $validated['quantity'];

      DB::beginTransaction();

      // Create the order item
      $orderItem = OrderItem::create([
        'order_id' => $validated['order_id'],
        'product_id' => $validated['product_id'],
        'quantity' => $validated['quantity'],
        'unit_price' => $validated['unit_price'],
        'wallet_credit_earned' => $walletCreditEarned,
        'total_price' => $totalPrice,
      ]);

      // Update order totals
      $this->updateOrderTotals($validated['order_id']);

      DB::commit();

      Toastr::success('Order item added successfully!');
      return back();

    } catch (\Exception $e) {
      DB::rollBack();
      Toastr::error('Error adding order item: ' . $e->getMessage());
      return back()->withInput();
    }
  }

  /**
   * Update an existing order item
   */
  public function updateItem(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'id' => ['required','integer','exists:order_items,id'],
        'quantity' => ['required','integer','min:1'],
        'unit_price' => ['required','numeric','min:0.01'],
      ]);

      if ($validator->fails()) {
        return back()
          ->withErrors($validator, 'editItemModal')
          ->withInput();
      }

      $validated = $validator->validated();

      $orderItem = OrderItem::findOrFail($validated['id']);
      $product = $orderItem->product;
      
      // Calculate wallet credit earned
      $walletCreditEarned = $product->wallet_credit * $validated['quantity'];
      
      // Calculate total price
      $totalPrice = $validated['unit_price'] * $validated['quantity'];

      DB::beginTransaction();

      // Update the order item
      $orderItem->update([
        'quantity' => $validated['quantity'],
        'unit_price' => $validated['unit_price'],
        'wallet_credit_earned' => $walletCreditEarned,
        'total_price' => $totalPrice,
      ]);

      // Update order totals
      $this->updateOrderTotals($orderItem->order_id);

      DB::commit();

      Toastr::success('Order item updated successfully!');
      return back();

    } catch (\Exception $e) {
      DB::rollBack();
      Toastr::error('Error updating order item: ' . $e->getMessage());
      return back()->withInput();
    }
  }

  /**
   * Delete an order item
   */
  public function deleteItem($id)
  {
    try {
      $orderItem = OrderItem::findOrFail($id);
      $orderId = $orderItem->order_id;

      DB::beginTransaction();

      $orderItem->delete();

      // Update order totals
      $this->updateOrderTotals($orderId);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Order item deleted successfully!'
      ]);

    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Error deleting order item: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Update order totals based on order items
   */
  private function updateOrderTotals($orderId)
  {
    $order = Order::findOrFail($orderId);
    $items = $order->items;

    // Calculate totals
    $subtotal = $items->sum('total_price');
    $walletCreditEarned = $items->sum('wallet_credit_earned');
    $itemsCount = $items->count();
    $unitsCount = $items->sum('quantity');
    $skusCount = $items->pluck('product_id')->unique()->count();

    // Calculate VAT (assuming 20% VAT rate - you can make this configurable)
    $vatRate = 0.20; // 20%
    $vatAmount = $subtotal * $vatRate;
    $totalAmount = $subtotal + $vatAmount;

    // Calculate paid and unpaid amounts
    // Paid amount = wallet credit used + sum of all payments
    $walletCreditUsed = (float)($order->wallet_credit_used ?? 0);
    $paymentsTotal = (float)($order->payments()->sum('amount') ?? 0);
    $paidAmount = $walletCreditUsed + $paymentsTotal;
    $outstandingAmount = $totalAmount - $paidAmount;
    $unpaidAmount = max(0, $outstandingAmount);

    // Determine payment status
    $paymentStatus = 'Due';
    if ($outstandingAmount <= 0) {
      $paymentStatus = 'Paid';
    } elseif ($paidAmount > 0) {
      $paymentStatus = 'Partial';
    }

    // Update order
    $order->update([
      'subtotal' => $subtotal,
      'vat_amount' => $vatAmount,
      'total_amount' => $totalAmount,
      'paid_amount' => $paidAmount,
      'unpaid_amount' => $unpaidAmount,
      'outstanding_amount' => $outstandingAmount,
      'payment_status' => $paymentStatus,
      'items_count' => $itemsCount,
      'units_count' => $unitsCount,
      'skus_count' => $skusCount,
    ]);
  }

  /**
   * Add payment to an order
   */
  public function addPayment(Request $request)
  {
    try {
      $validated = $request->validate([
        'order_id' => ['required', 'integer', 'exists:orders,id'],
        'date' => [
          'required',
          function ($attribute, $value, $fail) {
            if (empty($value)) {
              return;
            }
            // Try to parse d/m/Y H:i format (e.g., "12/11/2025 12:06")
            if (strpos($value, '/') !== false) {
              try {
                $parsed = Carbon::createFromFormat('d/m/Y H:i', $value);
                if ($parsed === false) {
                  try {
                    $parsed = Carbon::createFromFormat('d/m/Y', $value);
                    if ($parsed === false) {
                      $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format.');
                    }
                  } catch (\Exception $e) {
                    $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format.');
                  }
                }
              } catch (\Exception $e) {
                try {
                  $parsed = Carbon::createFromFormat('d/m/Y', $value);
                  if ($parsed === false) {
                    $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format.');
                  }
                } catch (\Exception $e2) {
                  $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format.');
                }
              }
            } else {
              // Try standard date formats
              try {
                $parsed = Carbon::parse($value);
                if ($parsed === false) {
                  $fail('Date must be a valid date.');
                }
              } catch (\Exception $e) {
                $fail('Date must be a valid date.');
              }
            }
          }
        ],
        'amount' => ['required', 'numeric', 'min:1'],
        'payment_method' => ['required', 'string', 'in:Cash,Bank,Outstanding'],
        'note' => ['nullable', 'string'],
      ], [
        'order_id.required' => 'Order ID is required',
        'order_id.exists' => 'Order not found',
        'date.required' => 'Date is required',
        'amount.required' => 'Amount is required',
        'amount.numeric' => 'Amount must be a number',
        'amount.min' => 'Amount must be at least 1',
        'payment_method.required' => 'Paying by is required',
        'payment_method.in' => 'Paying by must be Cash, Bank, or Outstanding',
      ]);

      $order = Order::findOrFail($validated['order_id']);
      
      // Refresh order to ensure we have the latest unpaid_amount
      $order->refresh();
      
      // Calculate current unpaid amount (before adding this payment)
      // unpaid_amount = total_amount - (wallet_credit_used + sum of all existing payments)
      $walletCreditUsed = (float)($order->wallet_credit_used ?? 0);
      $existingPaymentsTotal = (float)($order->payments()->sum('amount') ?? 0);
      $paidAmount = $walletCreditUsed + $existingPaymentsTotal;
      $unpaidAmount = max(0, ($order->total_amount ?? 0) - $paidAmount);

      // Validate that payment amount doesn't exceed unpaid amount
      if ($validated['amount'] > $unpaidAmount) {
        return response()->json([
          'success' => false,
          'message' => 'Validation failed',
          'errors' => [
            'amount' => ['Amount cannot be greater than payable amount (' . number_format($unpaidAmount, 2, '.', '') . ')']
          ]
        ], 422);
      }

      // Parse date
      $date = $validated['date'];
      if (strpos($date, '/') !== false) {
        try {
          $date = Carbon::createFromFormat('d/m/Y H:i', $date);
        } catch (\Exception $e) {
          $date = Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
        }
      } else {
        $date = Carbon::parse($date);
      }

      // Get reference number from order_ref table (pay column)
      $orderRef = OrderRef::orderBy('id', 'desc')->first();
      
      if (!$orderRef) {
        // Create initial order_ref record if it doesn't exist
        $orderRef = OrderRef::create([
          'so' => 1,
          'qa' => 1,
          'po' => 1,
          'pay' => 1,
        ]);
      }
      
      $referenceNo = $orderRef->pay ?? 1;
      
      // Increment pay for next payment
      $orderRef->update([
        'pay' => ($orderRef->pay ?? 0) + 1,
      ]);

      // Create payment in transaction
      DB::transaction(function () use ($validated, $date, $order, $referenceNo) {
        // Create payment
        $payment = \App\Models\Payment::create([
          'order_id' => $validated['order_id'],
          'date' => $date,
          'reference_no' => $referenceNo,
          'amount' => $validated['amount'],
          'payment_method' => $validated['payment_method'],
          'note' => $validated['note'] ?? null,
          'user_id' => auth()->id(),
        ]);

        // Refresh order to ensure we have latest data
        $order->refresh();
        
        // Update order totals (this will recalculate paid_amount including the new payment)
        $this->updateOrderTotals($order->id);
      });

      return response()->json([
        'success' => true,
        'message' => 'Payment added successfully!'
      ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error adding payment: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get payments for an order
   */
  public function getPayments($orderId)
  {
    try {
      $order = Order::findOrFail($orderId);
      
      $payments = \App\Models\Payment::where('order_id', $orderId)
        ->orderBy('date', 'desc')
        ->get()
        ->map(function ($payment) {
          return [
            'id' => $payment->id,
            'order_id' => $payment->order_id,
            'date' => $payment->date->toISOString(),
            'reference_no' => $payment->reference_no,
            'amount' => (float) $payment->amount,
            'payment_method' => $payment->payment_method,
            'note' => $payment->note,
          ];
        });

      return response()->json([
        'success' => true,
        'payments' => $payments
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error fetching payments: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Delete a payment
   */
  public function deletePayment($paymentId)
  {
    try {
      $payment = \App\Models\Payment::findOrFail($paymentId);
      $orderId = $payment->order_id;

      DB::transaction(function () use ($payment, $orderId) {
        // Delete payment
        $payment->delete();

        // Update order totals to recalculate paid_amount
        $this->updateOrderTotals($orderId);
      });

      return response()->json([
        'success' => true,
        'message' => 'Payment deleted successfully!'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error deleting payment: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get order statistics for dashboard widgets
   */
  public function getStatistics()
  {
    try {
      $stats = Order::selectRaw('
        COALESCE(SUM(total_amount), 0) as grand_total,
        COALESCE(SUM(paid_amount), 0) as paid,
        COALESCE(SUM(unpaid_amount), 0) as balance,
        SUM(CASE WHEN payment_status = "Due" THEN 1 ELSE 0 END) as due_count,
        SUM(CASE WHEN payment_status = "Partial" THEN 1 ELSE 0 END) as partial_count,
        SUM(CASE WHEN payment_status = "Paid" THEN 1 ELSE 0 END) as paid_count
      ')
      ->whereNull('deleted_at')
      ->first();

      return response()->json([
        'success' => true,
        'statistics' => [
          'grand_total' => (float) ($stats->grand_total ?? 0),
          'paid' => (float) ($stats->paid ?? 0),
          'balance' => (float) ($stats->balance ?? 0),
          'due_count' => (int) ($stats->due_count ?? 0),
          'partial_count' => (int) ($stats->partial_count ?? 0),
          'paid_count' => (int) ($stats->paid_count ?? 0),
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error fetching statistics: ' . $e->getMessage()
      ], 500);
    }
  }
}
