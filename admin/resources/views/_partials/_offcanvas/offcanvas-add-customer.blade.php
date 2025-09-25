 <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCustomerAdd"
    aria-labelledby="offcanvasCustomerAddLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasCustomerAddLabel" class="offcanvas-title">Add Customer</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body border-top mx-0 flex-grow-0">
      <form method="post" action="{{ route('customer.store') }}" class="customer-add pt-0" id="addCustomerForm">
        @csrf
        <div class="basic mb-4">
          <h6 class="mb-6">Basic Information</h6>
          <div class="mb-6 form-control-validation">
            <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" class="form-control" id="name" placeholder="Enter name"
              name="name" aria-label="Enter name" value="{{ old('name') }}" />
            @error('name','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="mb-6 form-control-validation">
            <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" id="email" class="form-control" placeholder="Enter email"
              aria-label="Enter email" name="email" value="{{ old('email') }}"/>
            @error('email','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="form-control-validation" >
            <label class="form-label" for="mobile">Mobile</label>
            <input type="text" maxlength="10" autocomplete="off" onkeypress="return /[0-9]/i.test(event.key)" id="mobile" class="form-control"
              placeholder="Enter mobile no" aria-label="Enter mobile no" name="mobile" value="{{ old('mobile') }}"/>
            @error('mobile','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="security mb-4 pt-4">
          <h6 class="mb-6">Security</h6>
          <div class="mb-6 form-control-validation form-password-toggle">
            <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
                <input type="password" autocomplete="off" class="form-control" id="password" placeholder="Enter password"
                name="password" aria-label="Enter password" />
              <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
             @error('password','add')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="mb-6 form-control-validation form-password-toggle">
            <label class="form-label" for="confirmPassword">Confirm Password <span class="text-danger">*</span></label>
             <div class="input-group input-group-merge">    
                <input type="password" autocomplete="off" id="confirmPassword" class="form-control" placeholder="Enter confirm password"
                aria-label="Enter confirm password" name="confirmPassword" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
            
          </div>
        </div>

        <div class="mb-6 pt-4">
          <h6 class="mb-6">Business Information</h6>
          <div class="mb-6">
            <label class="form-label" for="company-name">Company Name</label>
            <input type="text" autocomplete="off" id="company-name" class="form-control" placeholder="Enter company name"
              aria-label="Enter company name" name="companyName" value="{{ old('companyName') }}"/>
          </div>
          <div class="mb-6">
            <label class="form-label" for="vat-number">VAT Number</label>
            <input type="text" autocomplete="off" id="vat-number" class="form-control" placeholder="Enter VAT number" aria-label="Enter VAT number"
              name="vatNumber" value="{{ old('vatNumber') }}"/>
          </div>
          <div class="mb-6">
            <label class="form-label" for="business-reg-number">Business Registration Number</label>
            <input type="text" autocomplete="off" id="business-reg-number" class="form-control" placeholder="Enter business registration number"
              aria-label="Enter business registration number" name="businessRegistrationNumber" value="{{ old('businessRegistrationNumber') }}"/>
          </div>
          <div class="mb-6">
            <label class="form-label" for="status">Status</label>
            <select class="form-select select2" id="status" name="status">
              <option value="active" @selected(old('status') == 'active') @selected(old('status') == '')>Active</option>
              <option value="inactive" @selected(old('status') == 'inactive')>Inactive</option>
            </select>
          </div>
        </div>
        <div>
          <button type="submit" class="btn btn-primary me-sm-4 data-submit">Add</button>
          <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Discard</button>
        </div>
      </form>
    </div>
  </div>