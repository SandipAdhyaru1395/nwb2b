@extends('layouts/layoutMaster')

@section('title', 'Order Details')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js','resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/order-details.js',
'resources/assets/js/modal-edit-user.js'])
<script>
  document.addEventListener('DOMContentLoaded', function(){
    @if ($errors->addItemModal->any())
      const addModal = document.getElementById('addItemModal');
      if (addModal) new bootstrap.Modal(addModal).show();
    @endif

    @if ($errors->editItemModal->any())
      const editModal = document.getElementById('editItemModal');
      if (editModal) new bootstrap.Modal(editModal).show();
    @endif

    @if ($errors->editAddressModal->any())
      const editAddress = document.getElementById('editAddress');
      if (editAddress) new bootstrap.Modal(editAddress).show();
    @endif
  });
</script>
@endsection

@section('content')
<div
  class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
  <div class="d-flex flex-column justify-content-center">
    <div class="mb-1"><span class="h5">Order #{{ $order->order_number ?? '' }} 
      @if($order->payment_status == 'Paid')
        </span><span class="badge bg-label-success me-1 ms-2">{{ $order->payment_status ?? '' }}</span>
      @else
        <span class="badge bg-label-danger me-1 ms-2">{{ $order->payment_status ?? '' }}</span>  
      @endif


      @if($order->status == 'Cancelled')
        <span class="badge bg-label-danger">{{ $order->status ?? '' }}</span>
      @elseif($order->status == 'New')
        <span class="badge bg-primary">{{ $order->status ?? '' }}</span>
      @elseif($order->status == 'Processing')
        <span class="badge bg-warning">{{ $order->status ?? '' }}</span>
      @elseif($order->status == 'Shipped')
        <span class="badge bg-secondary">{{ $order->status ?? '' }}</span>
      @elseif($order->status == 'Delivered')
        <span class="badge bg-label-success">{{ $order->status ?? '' }}</span>
      @endif
    </div>
    <p class="mb-0">{{ \Carbon\Carbon::parse($order->order_date ?? now())->format('M d, Y') }}</p>
  </div>
  <div class="d-flex align-content-center flex-wrap gap-2">
    <button class="btn btn-label-danger delete-order">Delete Order</button>
  </div>
</div>

<!-- Order Details Table -->

<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card mb-6">
      
      <div class="card-datatable">
        <table class="datatables-order-details table mb-0">
          <thead>
            <tr>
              <th></th>
              <th class="w-70">products</th>
              <th class="w-10 text-success">credit earned</th>
              <th class="w-10">price</th>
              <th class="w-10">qty</th>
              <th>total</th>
              <th>actions</th>
            </tr>
          </thead>
        </table>
        <div class="d-flex justify-content-between align-items-center m-6 mb-2">
          @php
            $walletCreditEarned = $order->items->sum('wallet_credit_earned');
          @endphp
            <div class="credit-earned align-self-end">
              @if($walletCreditEarned > 0)
              <h6 class="mb-0  text-success">
                Total <i class="icon-base ti tabler-wallet icon-lg me-1 mb-2"></i> {{ $setting['currency_symbol'] ?? ''}}{{ number_format($walletCreditEarned, 2) }} Earned
              </h6>
              @endif
            </div>
          <div class="order-calculations">
            <div class="d-flex justify-content-start mb-2">
              <span class="w-px-200 text-end text-heading">Subtotal:</span>
              <h6 class="w-px-100 text-end mb-0">{{ $setting['currency_symbol'] ?? ''}}{{ number_format($order->subtotal ?? 0, 2) }}</h6>
            </div>
            
            <div class="d-flex justify-content-start mb-2">
              <span class="w-px-200 text-end text-heading">Wallet Discount:</span>
              <h6 class="w-px-100 text-end mb-0 ">
                -{{ $setting['currency_symbol'] ?? ''}}{{ number_format($order->wallet_credit_used ?? 0, 2) }}
              </h6>
            </div>
            <div class="d-flex justify-content-start mb-2">
              <span class="w-px-200 text-end text-heading">Delivery:</span>
              <h6 class="w-px-100 text-end mb-0">{{ $setting['currency_symbol'] ?? ''}}{{ number_format($order->delivery_charge ?? 0, 2) }}</h6>
            </div>
            <div class="d-flex justify-content-start mb-2">
              <span class="w-px-200 text-end text-heading">VAT:</span>
              <h6 class="w-px-100 text-end mb-0">{{ $setting['currency_symbol'] ?? ''}}{{ number_format($order->vat_amount ?? 0, 2) }}</h6>
            </div>
            <div class="d-flex justify-content-start">
              <h6 class="w-px-200 text-end mb-0">Total:</h6>
              <h6 class="w-px-100 text-end mb-0">{{ $setting['currency_symbol'] ?? ''}}{{ number_format($order->outstanding_amount ?? 0, 2) }}</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title m-0">Shipping activity</h5>
      </div>
      <div class="card-body pt-1">
        @php
          $mapTitle = function ($status) {
            return match($status) {
              'New' => 'Order was placed',
              'Processing' => 'Processing',
              'Shipped' => 'Dispatched',
              'Delivered' => 'Delivered',
              'Cancelled' => 'Cancelled',
              default => ucfirst($status)
            };
          };

          $mapDesc = function ($status) {
            // Admin-facing descriptions
            return match($status) {
              'New' => 'Order record created in system.',
              'Processing' => 'Warehouse/ops processing initiated.',
              'Shipped' => 'Consignment handed over to courier.',
              'Delivered' => 'Delivery confirmed by carrier/customer.',
              'Cancelled' => 'Order closed as cancelled.',
              default => ''
            };
          };

          // $mapDesc = function ($status) {
          //   return match($status) {
          //     'New' => 'Your order has been placed successfully',
          //     'Processing' => 'We are preparing your order',
          //     'Shipped' => 'Item has been picked up by courier',
          //     'Delivered' => 'Package delivered',
          //     'Cancelled' => 'This order has been cancelled.',
          //     default => ''
          //   };
          // };

          $events = $order->statusHistories->map(function ($h) use ($mapTitle) {
            return [
              'status' => $h->status,
              'title' => $mapTitle($h->status),
              'date' => \Carbon\Carbon::parse($h->created_at)->format('M d, Y H:i'),
            ];
          });

          if ($events->isEmpty()) {
            $events = collect([[
              'status' => $order->status ?? 'New',
              'title' => $mapTitle($order->status ?? 'New'),
              'date' => \Carbon\Carbon::parse($order->order_date ?? now())->format('M d, Y'),
            ]]);
          }
        @endphp

        @php
          // Canonical steps (always show all, dashed by default)
          $stepOrder = ['New','Processing','Shipped','Delivered'];
          // Map of history dates by actual recorded changes
          $histories = collect($order->statusHistories);
          $historyDates = $histories->keyBy('status')->map(fn($h) => \Carbon\Carbon::parse($h->created_at)->format('M d, Y H:i'));
          $historyStatuses = $histories->pluck('status')->all();
          
          // Find current order status index
          $currentStatusIndex = array_search($order->status, $stepOrder);
          if ($currentStatusIndex === false) {
            $currentStatusIndex = 0; // Default to first step if status not found
          }
        @endphp

        <ul class="timeline pb-0 mb-0">
          @foreach($stepOrder as $i => $status)
            @php
              // Highlight steps up to and including current status; future steps are dimmed
              $reached = $i <= $currentStatusIndex;
              $date = $historyDates[$status] ?? '';
              
              // Border class: if this is the current status, the line below should be dashed
              if ($i === $currentStatusIndex) {
                $borderClass = 'border-dashed'; // Current status gets dashed line below
              } else {
                $borderClass = $reached ? 'border-primary' : 'border-dashed';
              }
              
              $pointClass = $reached ? 'timeline-point-primary' : 'timeline-point-secondary';
              $title = $mapTitle($status);
              $desc = $mapDesc($status);
              $isLast = $i === count($stepOrder) - 1;
            @endphp
            <li class="timeline-item timeline-item-transparent {{ $borderClass }}">
              <span class="timeline-point {{ $pointClass }}"></span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <h6 class="mb-0">{{ $title }}</h6>
                  <small class="text-body-secondary">{{ $date }}</small>
                </div>
                <p class="mt-3 mb-{{ $isLast ? '0' : '3' }}">{{ $desc }}</p>
              </div>
            </li>
          @endforeach

          @if(in_array('Cancelled', $historyStatuses, true))
            <li class="timeline-item timeline-item-transparent border-danger">
              <span class="timeline-point timeline-point-danger"></span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <h6 class="mb-0">Cancelled</h6>
                  <small class="text-body-secondary">{{ $historyDates['Cancelled'] ?? '' }}</small>
                </div>
                <p class="mt-3 mb-0"></p>
              </div>
            </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title m-0">Update Order Status</h5>
      </div>
      <div class="card-body">
        <form method="post" action="{{ route('order.update') }}" class="row g-3 align-items-end">
          @csrf
          <input type="hidden" name="id" value="{{ $order->id }}">
          <div class="col-12">
            <label class="form-label">Payment Status</label>
            <select name="payment_status" class="form-select select2">
              @php $paymentOptions = config('variables.payment_status'); @endphp
              @foreach($paymentOptions as $opt)
                <option value="{{ $opt }}" {{ ($order->payment_status ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Order Status</label>
            <select name="status" class="form-select select2">
              @php $statusOptions = config('variables.order_status'); @endphp
              @foreach($statusOptions as $opt)
                <option value="{{ $opt }}" {{ ($order->status ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary w-100">Save</button>
          </div>
        </form>
      </div>
    </div>
    <div class="card mb-6">
      <div class="d-flex card-header justify-content-between">
        <h5 class="card-title m-0">Customer details</h5>
        <h6 class="mb-1"><a href="{{ url('customer/'. $order->customer_id.'/overview') }}">View</a></h6>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-start align-items-center mb-6">
          
          <div class="d-flex flex-column">
            <a href="javascript:void(0)" class="text-body text-nowrap">
              <h6 class="mb-0">{{ $order->customer->name ?? ($order->customer->company_name ?? 'N/A') }}</h6>
            </a>
            <span>Customer ID: #{{ $order->customer_id }}</span>
          </div>
        </div>
        <div class="d-flex justify-content-start align-items-center mb-6">
          <span class="avatar rounded-circle bg-label-success me-3 d-flex align-items-center justify-content-center"><i
              class="icon-base ti tabler-shopping-cart icon-lg"></i></span>
          <h6 class="text-nowrap mb-0">{{ $order->customer?->orders_count ?? 0 }} Orders</h6>
        </div>
        <div class="d-flex justify-content-between">
          <h6 class="mb-1">Contact info</h6>
          
        </div>
        <p class=" mb-1">Email: {{ $order->customer->email ?? '-' }}</p>
        <p class=" mb-0">Mobile: {{ $order->customer->phone ?? '-' }}</p>
      </div>
    </div>

    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between">
        <h5 class="card-title m-0">Address</h5>
        <h6 class="m-0"><a href=" javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editAddress">Edit</a>
        </h6>
      </div>
      <div class="card-body d-flex">
        <p class="mb-0">{{ $order->address_line1 ?? '' }}<br />{{ $order->address_line2 ?? '' }}<br />{{ $order->city ?? '' }}<br />{{ $order->country ?? '' }} {{ $order->zip_code ?? '' }}</p>
      </div>
    </div>
  </div>
</div>

@include('_partials._modals.modal-edit-order-address')
@include('_partials._modals.modal-add-order-product')
@include('_partials._modals.modal-edit-order-product')



<script>
  window.orderId = {{ $order->id ?? 'null' }};
  window.currencySymbol = @json($setting['currency_symbol'] ?? '');
  window.products = @json($products);
</script>

<!-- Modals -->


@endsection