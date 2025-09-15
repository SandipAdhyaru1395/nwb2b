<!-- User Sidebar -->
<div class="col-xl-4 col-lg-5 order-1 order-md-0">
  <!-- User Card -->
  <div class="card mb-6">
    <div class="card-body pt-12">
      <div class="user-avatar-section">
        <div class=" d-flex align-items-center flex-column">
          <img class="img-fluid rounded mb-4" src="{{ $user->image ?? asset('assets/img/avatars/1.png') }}" height="120" width="120"
            alt="User avatar" />
          <div class="user-info text-center">
            <h5>{{ $user->name }}</h5>
            @if($user->role)
              <span class="badge bg-label-secondary">{{ $user?->role?->name }}</span>
            @endif
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-around flex-wrap my-6 gap-0 gap-md-3 gap-lg-4">
        <div class="d-flex align-items-center me-5 gap-4">
          <div class="avatar">
            <div class="avatar-initial bg-label-primary rounded">
              <i class="icon-base ti tabler-checkbox icon-lg"></i>
            </div>
          </div>
          <div>
            <h5 class="mb-0">1.23k</h5>
            <span>Task Done</span>
          </div>
        </div>
        <div class="d-flex align-items-center gap-4">
          <div class="avatar">
            <div class="avatar-initial bg-label-primary rounded">
              <i class="icon-base ti tabler-briefcase icon-lg"></i>
            </div>
          </div>
          <div>
            <h5 class="mb-0">568</h5>
            <span>Project Done</span>
          </div>
        </div>
      </div>
      <h5 class="pb-4 border-bottom mb-4">Details</h5>
      <div class="info-container">
        <ul class="list-unstyled mb-6">
          <li class="mb-2">
            <span class="h6">Email:</span>
            <span>{{ $user->email }}</span>
          </li>
          <li class="mb-2">
            <span class="h6">Status:</span>
            <span>{{ ucfirst($user->status) }}</span>
          </li>
          @if($user->role)
            <li class="mb-2">
              <span class="h6">Role:</span>
              <span>{{ $user->role->name }}</span>
            </li>
          @endif
        </ul>
        <div class="d-flex justify-content-center">
          <a href="javascript:;" class="btn btn-primary me-4" data-bs-target="#editUser" data-bs-toggle="modal">Edit</a>
          <!-- <a href="javascript:;" class="btn btn-label-danger suspend-user">Suspend</a> -->
        </div>
      </div>
    </div>
  </div>
  <!-- /User Card -->
  <!-- Plan Card -->
  <div class="card mb-6 border border-2 border-primary rounded primary-shadow">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start">
        <span class="badge bg-label-primary">Standard</span>
        <div class="d-flex justify-content-center">
          <sub class="h5 pricing-currency mb-auto mt-1 text-primary">$</sub>
          <h1 class="mb-0 text-primary">99</h1>
          <sub class="h6 pricing-duration mt-auto mb-3 fw-normal">month</sub>
        </div>
      </div>
      <ul class="list-unstyled g-2 my-6">
        <li class="mb-2 d-flex align-items-center"><i
            class="icon-base ti tabler-circle-filled icon-10px text-secondary me-2"></i><span>10 Users</span></li>
        <li class="mb-2 d-flex align-items-center"><i
            class="icon-base ti tabler-circle-filled icon-10px text-secondary me-2"></i><span>Up to 10 GB storage</span>
        </li>
        <li class="mb-2 d-flex align-items-center"><i
            class="icon-base ti tabler-circle-filled icon-10px text-secondary me-2"></i><span>Basic Support</span></li>
      </ul>
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="h6 mb-0">Days</span>
        <span class="h6 mb-0">26 of 30 Days</span>
      </div>
      <div class="progress mb-1 bg-label-primary" style="height: 6px;">
        <div class="progress-bar" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0"
          aria-valuemax="100"></div>
      </div>
      <small>4 days remaining</small>
      <div class="d-grid w-100 mt-6">
        <button class="btn btn-primary" data-bs-target="#upgradePlanModal" data-bs-toggle="modal">Upgrade Plan</button>
      </div>
    </div>
  </div>
  <!-- /Plan Card -->
</div>
<!--/ User Sidebar -->