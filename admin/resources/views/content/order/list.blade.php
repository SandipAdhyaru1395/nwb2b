@extends('layouts/layoutMaster')

@section('title', 'Order List')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/quill/typography.scss',
'resources/assets/vendor/libs/quill/katex.scss',
'resources/assets/vendor/libs/quill/editor.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/quill/quill.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/order-list.js', 'resources/assets/js/payment-add.js', 'resources/assets/js/payment-view.js'])
@endsection

@section('page-style')
<style>
  .datatables-order {
    font-size: 0.8125rem;
  }
  .datatables-order thead th {
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
  }
  .datatables-order tbody td {
    padding: 0.4rem 0.6rem;
    font-size: 0.8125rem;
    vertical-align: middle;
  }
  .datatables-order .badge {
    font-size: 0.6875rem;
    padding: 0.2rem 0.4rem;
    line-height: 1.2;
  }
  .datatables-order .btn {
    padding: 0.2rem 0.4rem;
    font-size: 0.75rem;
  }
  .datatables-order .control {
    width: 25px !important;
    padding: 0.4rem 0.15rem !important;
    min-width: 25px !important;
  }
  .datatables-order .dt-checkboxes {
    width: 16px;
    height: 16px;
    margin: 0;
  }
  .datatables-order .text-nowrap {
    white-space: nowrap;
  }
  .card-datatable {
    padding: 0.75rem;
  }
  .datatables-order thead th:nth-child(10),
  .datatables-order thead th:nth-child(11) {
    width: 80px;
    min-width: 80px;
    text-align: center;
    line-height: 1.2;
  }
  .datatables-order thead th:nth-child(11) {
    width: 90px;
    min-width: 90px;
  }
  .datatables-order tbody td:nth-child(10),
  .datatables-order tbody td:nth-child(11) {
    text-align: center;
    width: 80px;
  }
  .datatables-order tbody td:nth-child(11) {
    width: 90px;
  }
</style>
@endsection

@section('content')
<!-- Order List Widget -->

<div class="card mb-6">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">
            <div>
              <h4 class="mb-0" id="widget-grand-total">0.00</h4>
              <p class="mb-0">Grand Total</p>
            </div>
            <span class="avatar me-sm-6">
              <span class="avatar-initial bg-label-secondary rounded text-heading">
                <i class="icon-base ti tabler-currency-dollar icon-26px text-heading"></i>
              </span>
            </span>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-6" />
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
            <div>
              <h4 class="mb-0" id="widget-paid">0.00</h4>
              <p class="mb-0">Paid</p>
            </div>
            <span class="avatar p-2 me-lg-6">
              <span class="avatar-initial bg-label-secondary rounded"><i
                  class="icon-base ti tabler-checks icon-26px text-heading"></i></span>
            </span>
          </div>
          <hr class="d-none d-sm-block d-lg-none" />
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start border-end pb-4 pb-sm-0 card-widget-3">
            <div>
              <h4 class="mb-0" id="widget-balance">0.00</h4>
              <p class="mb-0">Balance</p>
            </div>
            <span class="avatar p-2 me-sm-6">
              <span class="avatar-initial bg-label-secondary rounded"><i
                  class="icon-base ti tabler-ban icon-26px text-heading"></i></span>
            </span>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h4 class="mb-0" id="widget-payment-status-count">0</h4>
              <p class="mb-0">Due: <span id="widget-due-count">0</span> | Partial: <span id="widget-partial-count">0</span> | Paid: <span id="widget-paid-count">0</span></p>
            </div>
            <span class="avatar p-2">
              <span class="avatar-initial bg-label-secondary rounded"><i
                  class="icon-base ti tabler-file-invoice icon-26px text-heading"></i></span>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Order List Table -->
<div class="card">
  <div class="card-datatable table-responsive">
    <table class="datatables-order table border-top">
      <thead>
        <tr>
          <th></th>
          <th></th>
          <th>Date</th>
          <th>Reference No</th>
          <th>Customer</th>
          <th>Grand Total</th>
          <th>Paid</th>
          <th>Balance</th>
          <th>Total VAT</th>
          <th>Sale<br>Status</th>
          <th>Payment<br>Status</th>
          <th>actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

@include('_partials._modals.modal-add-payment')
@include('_partials._modals.modal-view-payments')

@endsection
