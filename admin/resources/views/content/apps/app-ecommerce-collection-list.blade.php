@extends('layouts/layoutMaster')

@section('title', 'Collection List')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss','resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js','resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-ecommerce-collection-list.js'])
@endsection

@section('content')
<!-- Product List Widget -->
<div class="card mb-6">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">
            <div>
              <p class="mb-1">Total Collections</p>
              <h4 class="mb-1">{{ $total_collections_count }}</h4>
            </div>
            <span class="avatar me-sm-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-smart-home icon-28px text-heading"></i></span>
            </span>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-6" />
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
            <div>
              <p class="mb-1">Active Collection</p>
              <h4 class="mb-1">{{ $active_collections_count }}</h4>
            </div>
            <span class="avatar p-2 me-lg-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-device-laptop icon-28px text-heading"></i></span>
            </span>
          </div>
          <hr class="d-none d-sm-block d-lg-none" />
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start border-end pb-4 pb-sm-0 card-widget-3">
            <div>
              <p class="mb-1">Inactive Collections</p>
              <h4 class="mb-1">{{ $inactive_collections_count }}</h4>
            </div>
            <span class="avatar p-2 me-sm-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-gift icon-28px text-heading"></i></span>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Product List Table -->
<div class="card">
  <div class="card-datatable">
    <table class="datatables-collections table">
      <thead class="border-top">
        <tr>
          <th></th>
          <th></th>
          <th>Collection</th>
          <th>Brand</th>
          <th>Categories</th>
          <th>Status</th>
          <th>actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

@endsection
