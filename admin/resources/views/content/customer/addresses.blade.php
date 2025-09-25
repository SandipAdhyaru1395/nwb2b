@extends('layouts/layoutMaster')

@section('title', 'Customer - Addresses')

@section('vendor-style')
@vite(['resources/assets/vendor/fonts/flag-icons.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/modal-customer-add-address.js','resources/assets/js/modal-customer-edit-address.js'])
<script>


$(document).ready(function () {

      $('#editCustomerAddress').on('show.bs.modal', function (e) {

        var id = $(e.relatedTarget).data('id');

        $.ajax({
          url: "{{ route('customer.address.edit') }}",
          type: 'GET',
          data: { id: id },
          success: function (response) {
            if (response.success) {
                const address = response.address;
                
                // Populate form fields
                $('#editCustomerAddressForm input[name="id"]').val(address.id);
                $('#editCustomerAddressForm input[name="name"]').val(address.name || '');
                $('#editCustomerAddressForm input[name="address_line1"]').val(address.address_line1 || '');
                $('#editCustomerAddressForm input[name="address_line2"]').val(address.address_line2 || '');
                $('#editCustomerAddressForm input[name="landmark"]').val(address.landmark || '');
                $('#editCustomerAddressForm input[name="city"]').val(address.city || '');
                $('#editCustomerAddressForm input[name="state"]').val(address.state || '');
                $('#editCustomerAddressForm input[name="zip_code"]').val(address.zip_code || '');
                
               $('#editCustomerAddressForm').find('select[name="country"]').val(address.country || '').trigger('change');

               $('#editCustomerAddressForm').find('input[name="type"][value="' + address.type + '"]').prop('checked', true);

               $('#editCustomerAddressForm').find('input[name="set_as_default"]').prop('checked', address.is_default);
            }

          }
        });
      });
    });

function deleteAddress(addressId) {
   if (addressId) {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {
        if(result.isConfirmed){
          window.location.href = baseUrl + 'customer/address/' + addressId + '/delete';
        }
      });
    }
}

function setDefaultAddress(addressId) {

  window.location.href = baseUrl + 'customer/address/' + addressId + '/set-default';
   
}

  document.addEventListener('DOMContentLoaded', function(){
    @if ($errors->add->any())
      const addCustomerAddress = document.getElementById('addCustomerAddress');
      if (addCustomerAddress) new bootstrap.Modal(addCustomerAddress).show();
    @endif
  });
</script>
@endsection

@section('content')
@include('content.customer.header')

<div class="row">
  @include('content.customer.sidebar')

  <!-- Customer Content -->
  <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
    <!-- Customer Pills -->
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row mb-6 row-gap-2 flex-wrap">
        <li class="nav-item">
          <a class="nav-link" href="{{ url('customer/'.$customer->id .'/overview') }}"><i
              class="icon-base ti tabler-user icon-sm me-1_5"></i>Overview</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('customer/'.$customer->id .'/security') }}"><i
              class="icon-base ti tabler-lock icon-sm me-1_5"></i>Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="javascript:void(0);"><i
              class="icon-base ti tabler-map-pin icon-sm me-1_5"></i>Addresses</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('customer/'.$customer->id .'/notifications') }}"><i
              class="icon-base ti tabler-bell icon-sm me-1_5"></i>Notifications</a>
        </li>
      </ul>
    </div>
    <!--/ Customer Pills -->

    <!-- Address accordion -->

    <div class="card card-action mb-6">
      <div class="card-header align-items-center py-6">
        <h5 class="card-action-title mb-0">Address Book</h5>
        <div class="card-action-element">
          <button class="btn btn-sm btn-label-primary" type="button" data-bs-toggle="modal"
            data-bs-target="#addCustomerAddress">Add new address</button>
        </div>
      </div>
      <div class="card-body">
        @if(isset($addresses) && $addresses->count() > 0)
          <div class="accordion accordion-flush accordion-arrow-left" id="ecommerceBillingAccordionAddress">
            @foreach($addresses as $index => $address)
              <div class="accordion-item {{ $index === 0 ? 'border-bottom' : ($index === $addresses->count() - 1 ? 'border-top-0' : 'border-bottom border-top-0') }}">
                <div class="accordion-header d-flex justify-content-between align-items-center flex-wrap flex-sm-nowrap"
                  id="heading{{ ucfirst($address->type) }}{{ $address->id }}">
                  <a class="accordion-button collapsed" data-bs-toggle="collapse"
                    data-bs-target="#ecommerceBillingAddress{{ ucfirst($address->type) }}{{ $address->id }}" 
                    aria-expanded="false" 
                    aria-controls="heading{{ ucfirst($address->type) }}{{ $address->id }}"
                    role="button">
                    <span>
                      <span class="d-flex gap-2 align-items-baseline">
                        <span class="h6 mb-1">{{ ucfirst($address->type) }}</span>
                        @if($address->is_default)
                          <span class="badge bg-label-success">Default Address</span>
                        @endif
                      </span>
                      <span class="mb-0">{{ $address->address_line1 }}</span>
                    </span>
                  </a>
                  <div class="d-flex gap-4 p-6 p-sm-0 pt-0 ms-1 ms-sm-0">
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editCustomerAddress" data-id="{{ $address->id }}">
                      <i class="icon-base ti tabler-edit text-body icon-md"></i>
                    </a>
                    <a href="javascript:void(0);" onclick="deleteAddress({{ $address->id }})">
                      <i class="icon-base ti tabler-trash text-body icon-md"></i>
                    </a>
                    <button class="btn p-0" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                      <i class="icon-base ti tabler-dots-vertical text-body icon-md"></i>
                    </button>
                    @if(!$address->is_default)
                    <ul class="dropdown-menu">
                        <li>
                          <a class="dropdown-item" href="javascript:void(0);" onclick="setDefaultAddress({{ $address->id }})">
                            Set as default address
                          </a>
                        </li>
                      </ul>
                      @endif
                  </div>
                </div>
                <div id="ecommerceBillingAddress{{ ucfirst($address->type) }}{{ $address->id }}" 
                     class="accordion-collapse collapse" 
                     aria-labelledby="heading{{ ucfirst($address->type) }}{{ $address->id }}"
                     data-bs-parent="#ecommerceBillingAccordionAddress">
                  <div class="accordion-body ps-6 ms-1">
                    <h6 class="mb-1">{{ $address->name ?: $customer->name }}</h6>
                    <p class="mb-1">{{ $address->address_line1 }},</p>
                    @if($address->address_line2)
                      <p class="mb-1">{{ $address->address_line2 }},</p>
                    @endif
                    @if($address->landmark)
                      <p class="mb-1">{{ $address->landmark }},</p>
                    @endif
                    <p class="mb-1">{{ $address->city }}, {{ $address->state }} {{ $address->zip_code }},</p>
                    <p class="mb-1">{{ $address->country }}</p>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-4">
            <p class="text-muted">No addresses found. Add your first address using the button above.</p>
          </div>
        @endif
      </div>
    </div>
    <!-- Address accordion -->
    
  </div>
  <!--/ Customer Content -->
</div>

<!-- Modal -->
@include('_partials/_modals/modal-customer-add-address')
@include('_partials/_modals/modal-customer-edit-address')
<!-- /Modal -->
@endsection