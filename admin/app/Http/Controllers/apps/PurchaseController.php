<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\OrderRef;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\WarehouseProductSyncService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function index()
    {
        $data['total_purchases_count'] = Purchase::all()->count();
        $data['today_purchases_count'] = Purchase::whereDate('date', today())->count();
        $data['this_month_purchases_count'] = Purchase::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)->count();

        return view('content.purchase.list', $data);
    }

    public function add()
    {
        $data['products'] = Product::where('is_active', 1)->orderBy('name')->get();
        $data['suppliers'] = Supplier::where('is_active', 1)->orderBy('company')->get();
        return view('content.purchase.add', $data);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'deliver' => ['nullable', 'in:purchase,delivery_note'],
            'shipping_charge' => ['nullable', 'numeric', 'min:0'],
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
            'reference_no' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'note' => ['nullable', 'string'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'numeric', 'min:1'],
            'products.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'supplier_id.required' => 'Supplier is required',
            'supplier_id.exists' => 'Selected supplier does not exist',
            'deliver.required' => 'Deliver is required',
            'deliver.in' => 'Deliver must be either Purchase or Delivery Note',
            'date.required' => 'Date is required',
            'products.required' => 'At least one product is required',
            'products.min' => 'At least one product is required',
            'products.*.product_id.required' => 'Product is required',
            'products.*.product_id.exists' => 'Selected product does not exist',
            'products.*.quantity.required' => 'Quantity is required',
            'products.*.quantity.numeric' => 'Quantity must be a number',
            'products.*.quantity.min' => 'Quantity must be greater than 0',
            'products.*.unit_cost.required' => 'Cost price is required',
            'products.*.unit_cost.numeric' => 'Cost price must be a number',
            'products.*.unit_cost.min' => 'Cost price must be 0 or greater',
            'document.mimes' => 'Document must be a file of type: pdf, doc, docx, jpg, jpeg, png',
            'document.max' => 'Document must not be larger than 10MB',
        ]);

        // Handle document upload
        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('purchases/documents', 'public');
        }

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

        $default_reference_no = OrderRef::orderBy('id', 'desc')->first();
        if ($validated['reference_no']==null) {
            $reference_no = $default_reference_no->po ?? 1;
            $default_reference_no->update([
                'po' => ($default_reference_no->po ?? 0) + 1,
            ]);

        } else {
            $reference_no = $validated['reference_no'];
        }

        // Normalize products array (in case keys are product IDs)
        $products = array_filter($validated['products'], function($productData) {
            return isset($productData['product_id']);
        });

        // Process everything in transaction for data integrity
        $userId = Auth::id();
        DB::transaction(function () use ($date, $reference_no, $documentPath, $request, $products, $userId, $validated) {
            // Create purchase
            $purchase = Purchase::create([
                'date' => $date,
                'reference_no' => $reference_no ?? null,
                'supplier_id' => $validated['supplier_id'],
                'deliver' => $validated['deliver'],
                'shipping_charge' => $validated['shipping_charge'] ?? 0,
                'document' => $documentPath,
                'note' => $request->note ?? null,
                'user_id' => $userId,
            ]);

            $adjustments = [];
            $purchaseItemsForCost = [];
            $subTotal = 0;
            
            // Get current warehouse product states BEFORE processing adjustments
            $warehouseProducts = \App\Models\WarehousesProduct::whereIn('product_id', array_column($products, 'product_id'))
                ->get()
                ->keyBy('product_id');
            
            // Create purchase items and collect adjustments
            foreach ($products as $productData) {
                $quantity = (float) $productData['quantity'];
                $unitCost = (float) ($productData['unit_cost'] ?? 0);
                $subtotal = round($quantity * $unitCost, 2);
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $subTotal += $subtotal;

                $adjustments[] = [
                    'product_id' => $productData['product_id'],
                    'type' => 'addition',
                    'quantity' => $quantity,
                ];

                // Capture old values for average cost calculation
                $warehouseProduct = $warehouseProducts->get($productData['product_id']);
                $purchaseItemsForCost[] = [
                    'product_id' => $productData['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'old_quantity' => $warehouseProduct ? (float) $warehouseProduct->quantity : 0,
                    'old_avg_cost' => $warehouseProduct ? (float) $warehouseProduct->avg_cost : 0,
                ];
            }

            // Calculate total amount (sub_total + shipping_charge)
            $shippingCharge = (float) ($validated['shipping_charge'] ?? 0);
            $totalAmount = $subTotal + $shippingCharge;

            // Update purchase with sub_total and total_amount
            $purchase->update([
                'sub_total' => round($subTotal, 2),
                'total_amount' => round($totalAmount, 2),
            ]);

            // Process all adjustments at once (updates quantity)
            WarehouseProductSyncService::processAdjustments($adjustments);
            
            // Update average cost after quantity adjustments (uses old values to calculate weighted average)
            WarehouseProductSyncService::processAverageCostUpdates($purchaseItemsForCost);
        });

        Toastr::success('Purchase created successfully!');
        return redirect()->route('purchase.list');
    }

    public function edit($id)
    {
        $data['purchase'] = Purchase::with(['items.product', 'supplier'])->findOrFail($id);
        $data['products'] = Product::where('is_active', 1)->orderBy('name')->get();
        $data['suppliers'] = Supplier::where('is_active', 1)->orderBy('company')->get();
        return view('content.purchase.edit', $data);
    }

    public function update(Request $request)
    {
        
        $validated = $request->validate([
            'id' => ['required', 'exists:purchases,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'deliver' => ['nullable', 'in:purchase,delivery_note'],
            'shipping_charge' => ['nullable', 'numeric', 'min:0'],
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
            'reference_no' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'note' => ['nullable', 'string'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'numeric', 'min:1'],
            'products.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'id.required' => 'Purchase ID is required',
            'id.exists' => 'Purchase not found',
            'supplier_id.required' => 'Supplier is required',
            'supplier_id.exists' => 'Selected supplier does not exist',
            'deliver.required' => 'Deliver is required',
            'deliver.in' => 'Deliver must be either Purchase or Delivery Note',
            'date.required' => 'Date is required',
            'products.required' => 'At least one product is required',
            'products.min' => 'At least one product is required',
            'products.*.product_id.required' => 'Product is required',
            'products.*.product_id.exists' => 'Selected product does not exist',
            'products.*.quantity.required' => 'Quantity is required',
            'products.*.quantity.numeric' => 'Quantity must be a number',
            'products.*.quantity.min' => 'Quantity must be greater than 0',
            'products.*.unit_cost.required' => 'Cost price is required',
            'products.*.unit_cost.numeric' => 'Cost price must be a number',
            'products.*.unit_cost.min' => 'Cost price must be 0 or greater',
            'shipping_charge.numeric' => 'Shipping charge must be a number',
            'shipping_charge.min' => 'Shipping charge must be 0 or greater',
            'document.mimes' => 'Document must be a file of type: pdf, doc, docx, jpg, jpeg, png',
            'document.max' => 'Document must not be larger than 10MB',
        ]);

        $purchase = Purchase::with('items')->findOrFail($request->id);

        // Prepare old adjustments for reversal
        $oldAdjustments = $purchase->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'type' => 'addition',
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // Handle document upload
        $documentPath = $purchase->document;
        if ($request->hasFile('document')) {
            // Delete old document
            if ($documentPath) {
                Storage::disk('public')->delete($documentPath);
            }
            $documentPath = $request->file('document')->store('purchases/documents', 'public');
        }

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

        // Update purchase
        $purchase->update([
            'date' => $date,
            'reference_no' => $validated['reference_no'] ?? null,
            'supplier_id' => $validated['supplier_id'],
            'deliver' => $validated['deliver'],
            'shipping_charge' => $validated['shipping_charge'] ?? 0,
            'document' => $documentPath,
            'note' => $request->note ?? null,
        ]);

        // Normalize products array (in case keys are product IDs)
        $products = array_filter($validated['products'], function($productData) {
            return isset($productData['product_id']);
        });

        // Get old purchase items for cost reversion
        $oldPurchaseItemsForCost = $purchase->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => (float) $item->quantity,
                'unit_cost' => (float) $item->unit_cost,
            ];
        })->toArray();

        // Process update in transaction
        DB::transaction(function () use ($purchase, $oldAdjustments, $oldPurchaseItemsForCost, $products, $validated) {
            // Get current warehouse product states BEFORE reverting
            $warehouseProductsBeforeRevert = \App\Models\WarehousesProduct::whereIn('product_id', array_column($oldPurchaseItemsForCost, 'product_id'))
                ->get()
                ->keyBy('product_id');
            
            // Revert average cost updates first (needs current state)
            $oldItemsWithCurrentState = [];
            foreach ($oldPurchaseItemsForCost as $item) {
                $wp = $warehouseProductsBeforeRevert->get($item['product_id']);
                $oldItemsWithCurrentState[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'old_quantity' => $wp ? (float) $wp->quantity : 0,
                    'old_avg_cost' => $wp ? (float) $wp->avg_cost : 0,
                ];
            }
            WarehouseProductSyncService::revertAverageCostUpdates($oldItemsWithCurrentState);
            
            // Revert previous stock changes
            WarehouseProductSyncService::revertAdjustments($oldAdjustments);

            // Delete old items
            $purchase->items()->delete();

            // Get warehouse product states AFTER revert, BEFORE new adjustments
            $warehouseProductsAfterRevert = \App\Models\WarehousesProduct::whereIn('product_id', array_column($products, 'product_id'))
                ->get()
                ->keyBy('product_id');

            // Prepare new adjustments and cost updates
            $newAdjustments = [];
            $newPurchaseItemsForCost = [];
            $subTotal = 0;
            foreach ($products as $productData) {
                $quantity = (float) $productData['quantity'];
                $unitCost = (float) ($productData['unit_cost'] ?? 0);
                $subtotal = round($quantity * $unitCost, 2);
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $newAdjustments[] = [
                    'product_id' => $productData['product_id'],
                    'type' => 'addition',
                    'quantity' => $quantity,
                ];

                // Capture old values for average cost calculation (after revert)
                $warehouseProduct = $warehouseProductsAfterRevert->get($productData['product_id']);
                $newPurchaseItemsForCost[] = [
                    'product_id' => $productData['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'old_quantity' => $warehouseProduct ? (float) $warehouseProduct->quantity : 0,
                    'old_avg_cost' => $warehouseProduct ? (float) $warehouseProduct->avg_cost : 0,
                ];

                $subTotal += $subtotal;
            }
            
            // Calculate total amount (sub_total + shipping_charge)
            $shippingCharge = (float) ($validated['shipping_charge'] ?? 0);
            $totalAmount = $subTotal + $shippingCharge;

            // Apply new adjustments (updates quantity)
            WarehouseProductSyncService::processAdjustments($newAdjustments);
            
            // Update average cost after quantity adjustments
            WarehouseProductSyncService::processAverageCostUpdates($newPurchaseItemsForCost);

            // Update purchase with sub_total and total_amount
            $purchase->update([
                'sub_total' => round($subTotal, 2),
                'total_amount' => round($totalAmount, 2),
            ]);
        });

        Toastr::success('Purchase updated successfully!');
        return redirect()->route('purchase.list');
    }

    public function delete($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        // Prepare adjustments for reversal
        $adjustments = $purchase->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'type' => 'addition',
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // Prepare purchase items for cost reversion
        $purchaseItemsForCost = $purchase->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => (float) $item->quantity,
                'unit_cost' => (float) $item->unit_cost,
            ];
        })->toArray();

        DB::transaction(function () use ($adjustments, $purchaseItemsForCost, $purchase) {
            // Get current warehouse product states BEFORE reverting
            $warehouseProducts = \App\Models\WarehousesProduct::whereIn('product_id', array_column($purchaseItemsForCost, 'product_id'))
                ->get()
                ->keyBy('product_id');
            
            // Prepare items with current state for cost reversion
            $itemsWithCurrentState = [];
            foreach ($purchaseItemsForCost as $item) {
                $wp = $warehouseProducts->get($item['product_id']);
                $itemsWithCurrentState[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'old_quantity' => $wp ? (float) $wp->quantity : 0,
                    'old_avg_cost' => $wp ? (float) $wp->avg_cost : 0,
                ];
            }
            
            // Revert average cost updates first
            WarehouseProductSyncService::revertAverageCostUpdates($itemsWithCurrentState);
            
            // Revert stock changes
            WarehouseProductSyncService::revertAdjustments($adjustments);

            // Delete document if exists
            if ($purchase->document) {
                Storage::disk('public')->delete($purchase->document);
            }

            $purchase->delete();
        });

        Toastr::success('Purchase deleted successfully!');
        return redirect()->back();
    }

    public function ajaxList(Request $request)
    {
        $query = Purchase::with(['user', 'supplier'])
            ->select([
                'id',
                'date',
                'reference_no',
                'supplier_id',
                'total_amount',
                'note',
                'user_id',
                'created_at',
            ])
            ->orderBy('id', 'desc');

        return DataTables::eloquent($query)
            ->addColumn('reference_no_display', function ($purchase) {
                return $purchase->reference_no ?? 'N/A';
            })
            ->addColumn('supplier_name', function ($purchase) {
                if ($purchase->supplier) {
                    return $purchase->supplier->company ?? $purchase->supplier->full_name ?? 'Supplier #' . $purchase->supplier_id;
                }
                return 'N/A';
            })
            ->addColumn('total_amount_display', function ($purchase) {
                return $purchase->total_amount ? number_format($purchase->total_amount, 2) : '0.00';
            })
            ->addColumn('user_name', function ($purchase) {
                return $purchase->user ? $purchase->user->name : 'N/A';
            })
            ->addColumn('date_formatted', function ($purchase) {
                return optional($purchase->date)->format('d/m/Y H:i');
            })
            ->addColumn('note_display', function ($purchase) {
                return $purchase->note ? strip_tags($purchase->note) : '';
            })
            ->addColumn('actions', function ($purchase) {
                $editUrl = route('purchase.edit', $purchase->id);
                return '<div class="d-inline-block text-nowrap">' .
                       '<a href="' . $editUrl . '" class="rounded-pill waves-effect btn-icon"><button class="btn btn-text-secondary "><i class="icon-base ti tabler-edit icon-22px"></i></button></a> ' .
                       '<a href="javascript:;" onclick="deletePurchase(' . $purchase->id . ')" class="rounded-pill waves-effect btn-icon"><button class="btn"><i class="icon-base ti tabler-trash icon-22px"></i></button></a>' .
                       '</div>';
            })
            ->filterColumn('reference_no_display', function ($query, $keyword) {
                $query->where('reference_no', 'like', "%{$keyword}%");
            })
            ->filterColumn('supplier_name', function ($query, $keyword) {
                $query->whereHas('supplier', function ($q) use ($keyword) {
                    $q->where('company', 'like', "%{$keyword}%")
                      ->orWhere('full_name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function showAjax($id)
    {
        $purchase = Purchase::with(['items.product', 'user', 'supplier'])->findOrFail($id);
        $html = view('_partials._modals.modal-purchase-show', compact('purchase'))->render();
        return response()->json(['html' => $html]);
    }

    public function searchAjax(Request $request)
    {
        $q = trim($request->get('q', ''));
        $limit = (int) $request->get('limit', 10);

        $query = Product::select(['id', 'name', 'sku', 'price','cost_price','image_url','stock_quantity'])
            ->where('is_active', 1);

        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        $products = $query->orderBy('id', 'desc')->limit($limit)->get();

        return response()->json([
            'results' => $products->map(function($p) {
                return [
                    'id' => $p->id,
                    'text' => $p->name . ' (' . $p->sku . ')',
                    'price' => $p->price,
                    'unit_cost' => $p->cost_price,
                    'image_url' => $p->image_url,
                    'stock_quantity' => $p->stock_quantity ?? 0,
                ];
            })
        ]);
    }
    
}
