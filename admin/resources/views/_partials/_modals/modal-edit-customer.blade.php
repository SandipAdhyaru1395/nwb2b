<!-- Edit Shipping Address Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-add-new-address">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-6">
          <h4 class="address-title mb-2">Edit Customer</h4>
        </div>
        <form method="post" action="{{ route('customer.update') }}" class="customer-add pt-0" id="editCustomerForm">
        @csrf
        <input type="hidden" name="id" value="{{ $customer->id }}">
        <div class="basic mb-4">
          <div class="mb-6 form-control-validation">
            <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" class="form-control" id="name" placeholder="Enter name"
              name="name" aria-label="Enter name" value="{{ old('name') ?? $customer->name }}" />
            @error('name','edit')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="mb-6 form-control-validation">
            <label class="form-label" for="company-name">Company Name <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" id="company-name" class="form-control" placeholder="Enter company name"
              aria-label="Enter company name" name="companyName" value="{{ old('companyName') ?? $customer->company_name }}"/>
          </div>
          <div class="mb-6 form-control-validation">
            <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" id="email" class="form-control" placeholder="Enter email"
              aria-label="Enter email" name="email" value="{{ old('email') ?? $customer->email }}"/>
            @error('email','edit')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="mb-6 form-control-validation" >
            <label class="form-label" for="mobile">Mobile <span class="text-danger">*</span></label>
            <input type="text" maxlength="10" autocomplete="off" onkeypress="return /[0-9]/i.test(event.key)" id="mobile" class="form-control"
              placeholder="Enter mobile no" aria-label="Enter mobile no" name="mobile" value="{{ old('mobile') ?? $customer->phone }}"/>
            @error('mobile','edit')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="mb-6">
            <label class="form-label" for="vat-number">VAT Number</label>
            <input type="text" autocomplete="off" id="vat-number" class="form-control" placeholder="Enter VAT number" aria-label="Enter VAT number"
              name="vatNumber" value="{{ old('vatNumber') ?? $customer->vat_number }}"/>
          </div>
          <div class="mb-6 form-control-validation">
            <label class="form-label" for="status">Status</label>
            <select class="form-select select2" id="status" name="status">
              <option value="active" @selected(old('status') == 'active' || (old('status') == '' && $customer->is_active == 1))>Active</option>
              <option value="inactive" @selected(old('status') == 'inactive' || (old('status') == '' && $customer->is_active == 0))>Inactive</option>
            </select>
          </div>
        </div>
        <div>
          <button type="submit" class="btn btn-primary me-sm-4 data-submit">Update</button>
          <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Discard</button>
        </div>
      </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add New Address Modal -->