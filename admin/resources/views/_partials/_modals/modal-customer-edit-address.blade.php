<!-- Edit Shipping Address Modal -->
<div class="modal fade" id="editCustomerAddress" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-add-new-address">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-6">
          <h4 class="address-title mb-2">Edit Address</h4>
        </div>
        <form method="post" action="{{ route('customer.address.update') }}" class="row g-6" id="editCustomerAddressForm">
          @csrf
          <input type="hidden" name="customer_id" value="{{ $customer->id ?? '' }}">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="is_default" value="0">
          <div class="col-12">
            <label class="form-label" for="name">Contact Name</label>
            <input type="text" autocomplete="off" id="name" name="name" value="{{ $customer->name ?? '' }}" class="form-control" placeholder="Contact name for this address" />
            @error('name','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 form-control-validation">
            <label class="form-label" for="address_line1">Address Line 1 <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" id="address_line1" name="address_line1" value="" class="form-control" placeholder="12, Business Park" />
            @error('address_line1','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12">
            <label class="form-label" for="address_line2">Address Line 2</label>
            <input type="text" autocomplete="off" id="address_line2" name="address_line2" value="" class="form-control" placeholder="Mall Road" />
            @error('address_line2','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="city">City <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" id="city" name="city" value="" class="form-control" placeholder="Los Angeles" />
            @error('city','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="state">State</label>
            <input type="text" autocomplete="off" id="state" name="state" value="" class="form-control" placeholder="California" />
            @error('state','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 form-control-validation">
            <label class="form-label" for="country">Country</label>
            <input type="text" autocomplete="off" id="country" name="country" value="{{ $customer->country ?? '' }}" class="form-control" placeholder="United States" />
            @error('country','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="zip_code">Zip Code <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" id="zip_code" name="zip_code" value="" class="form-control" placeholder="99950" />
            @error('zip_code','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          
          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="set_as_default" id="setAsDefault" value="1">
              <label class="form-check-label" for="setAsDefault">
                Set as default address
              </label>
            </div>
          </div>
          
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-3">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add New Address Modal -->