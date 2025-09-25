<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
  public function index()
  {
    return view('content.order.list');
  }

  public function getOrderDetails()
  {
    return view('content.order.details');
  }

  public function edit($id)
  {
    $order = Order::with(['customer', 'items.product', 'statusHistories'])->findOrFail($id);
    $products = Product::where('is_active', 1)->select('id', 'name', 'sku', 'price', 'wallet_credit', 'image_url')->get();

    return view('content.order.details', [
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
    $validated = $request->validate([
      'id' => ['required', 'exists:orders,id'],
      'order_number' => ['sometimes','string'],
      'order_date' => ['sometimes','date'],
      'payment_status' => ['sometimes','string'],
      'status' => ['sometimes','string'],

      // billing
      'b_address_type' => ['sometimes','nullable','string','max:50'],
      'b_country' => ['sometimes','nullable','string','max:100'],
      'b_address_line1' => ['sometimes','nullable','string'],
      'b_address_line2' => ['sometimes','nullable','string'],
      'b_landmark' => ['sometimes','nullable','string'],
      'b_city' => ['sometimes','nullable','string','max:100'],
      'b_state' => ['sometimes','nullable','string','max:100'],
      'b_zip_code' => ['sometimes','nullable','string','max:20'],

      // shipping
      's_address_type' => ['sometimes','nullable','string','max:50'],
      's_country' => ['sometimes','nullable','string','max:100'],
      's_address_line1' => ['sometimes','nullable','string'],
      's_address_line2' => ['sometimes','nullable','string'],
      's_landmark' => ['sometimes','nullable','string'],
      's_city' => ['sometimes','nullable','string','max:100'],
      's_state' => ['sometimes','nullable','string','max:100'],
      's_zip_code' => ['sometimes','nullable','string','max:20'],
    ]);

    $order = Order::findOrFail($validated['id']);
    $originalStatus = $order->status;
    $order->fill($validated);
    $order->save();

    if (isset($validated['status']) && $validated['status'] !== $originalStatus) {
      \App\Models\OrderStatusHistory::create([
        'order_id' => $order->id,
        'status' => $validated['status'],
        'note' => null,
      ]);
    }

    Toastr::success('Order updated successfully!');
    return redirect()->route('order.edit', ['id' => $order->id]);
  }

  public function ajaxList(Request $request)
  {
    $query = Order::select([
      'orders.id',
      'orders.order_number',
      'orders.order_date',
      'orders.payment_status',
      'orders.status as order_status',
      'customers.name as customer_name',
      'customers.email as customer_email'
    ])->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
      ->whereNull('orders.deleted_at')
      ->orderBy('orders.id', 'desc');

    return DataTables::eloquent($query)
      ->filterColumn('order_number', function ($query, $keyword) {
        $query->where('orders.order_number', 'like', "%{$keyword}%");
      })
      ->filterColumn('customer_name', function ($query, $keyword) {
        $query->where('customers.name', 'like', "%{$keyword}%");
      })
      ->filterColumn('payment_status', function ($query, $keyword) {
        $query->where('orders.payment_status', 'like', "%{$keyword}%");
      })
      ->filterColumn('order_status', function ($query, $keyword) {
        $query->where('orders.status', 'like', "%{$keyword}%");
      })
      ->filterColumn('order_date', function ($query, $keyword) {
        $query->where('orders.order_date', 'like', "%{$keyword}%");
      })
      ->orderColumn('order_number', function ($query, $order) {
        $query->orderBy('orders.order_number', $order);
      })
      ->toJson();
  }

  public function delete($id)
  {
    $order = Order::findOrFail($id);
    // Optionally cascade delete items
    DB::transaction(function () use ($order) {
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

    // Update order
    $order->update([
      'subtotal' => $subtotal,
      'vat_amount' => $vatAmount,
      'total_amount' => $totalAmount,
      'items_count' => $itemsCount,
      'units_count' => $unitsCount,
      'skus_count' => $skusCount,
    ]);
  }
}
