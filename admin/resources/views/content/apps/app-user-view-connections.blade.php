@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'User View - Pages')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/page-user-view.scss')
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/modal-edit-user.js', 'resources/assets/js/app-user-view.js'])
@endsection

@section('content')
<div class="row">
  @include('content.apps.user-account-sidebar')

  <!-- User Content -->
  <div class="col-xl-8 col-lg-7 order-0 order-md-1">
    <!-- User Pills -->
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row mb-6 flex-wrap row-gap-2">
        <li class="nav-item">
          <a class="nav-link" href="{{ url('user/view/account/'.$user->id) }}"><i class="icon-base ti tabler-user-check icon-sm me-1_5"></i>Account</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('user/view/security/'.$user->id) }}"><i class="icon-base ti tabler-lock icon-sm me-1_5"></i>Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('user/view/billing/'.$user->id) }}"><i class="icon-base ti tabler-bookmark icon-sm me-1_5"></i>Billing & Plans</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('user/view/notifications/'.$user->id) }}"><i class="icon-base ti tabler-bell icon-sm me-1_5"></i>Notifications</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="javascript:void(0);"><i class="icon-base ti tabler-link icon-sm me-1_5"></i>Connections</a>
        </li>
      </ul>
    </div>
    <!--/ User Pills -->
    <!-- Connected Accounts -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="mb-0">Connected Accounts</h5>
        <p class="my-0 card-subtitle">Display content from your connected accounts on your site</p>
      </div>
      <div class="card-body pt-0">
        <div class="d-flex mb-4">
          <div class="flex-shrink-0">
            <img src="{{ asset('assets/img/icons/brands/google.png') }}" alt="google" class="me-4" height="36" />
          </div>
          <div class="flex-grow-1 d-flex align-items-center justify-content-between">
            <div class="mb-sm-0 mb-2">
              <h6 class="mb-50">Google</h6>
              <span class="small">Calendar and contacts</span>
            </div>
            <div>
              <div class="form-check form-switch mb-0">
                <input type="checkbox" class="form-check-input" checked />
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex mb-4 align-items-center">
          <div class="flex-shrink-0">
            <img src="{{ asset('assets/img/icons/brands/slack.png') }}" alt="slack" class="me-4" height="36" />
          </div>
          <div class="flex-grow-1 d-flex align-items-center justify-content-between">
            <div class="mb-sm-0 mb-2">
              <h6 class="mb-50">Slack</h6>
              <span class="small">Communication</span>
            </div>
            <div>
              <div class="form-check form-switch mb-0">
                <input type="checkbox" class="form-check-input" />
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex mb-4 align-items-center">
          <div class="flex-shrink-0">
            <img src="{{ asset('assets/img/icons/brands/github.png') }}" alt="github" class="me-4" height="36" />
          </div>
          <div class="flex-grow-1 d-flex align-items-center justify-content-between">
            <div class="mb-sm-0 mb-2">
              <h6 class="mb-50">Github</h6>
              <span class="small">Manage your Git repositories</span>
            </div>
            <div>
              <div class="form-check form-switch mb-0">
                <input type="checkbox" class="form-check-input" checked />
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex mb-4 align-items-center">
          <div class="flex-shrink-0">
            <img src="{{ asset('assets/img/icons/brands/mailchimp.png') }}" alt="mailchimp" class="me-4" height="36" />
          </div>
          <div class="flex-grow-1 d-flex align-items-center justify-content-between">
            <div class="mb-sm-0 mb-2">
              <h6 class="mb-50">Mailchimp</h6>
              <span class="small">Email marketing service</span>
            </div>
            <div>
              <div class="form-check form-switch mb-0">
                <input type="checkbox" class="form-check-input" checked />
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <img src="{{ asset('assets/img/icons/brands/asana.png') }}" alt="asana" class="me-4" height="36" />
          </div>
          <div class="flex-grow-1 d-flex align-items-center justify-content-between">
            <div class="mb-sm-0 mb-2">
              <h6 class="mb-50">Asana</h6>
              <span class="small">Communication</span>
            </div>
            <div>
              <div class="form-check form-switch mb-0">
                <input type="checkbox" class="form-check-input" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- /Connected Accounts -->

    <!-- Social Accounts -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="mb-0">Social Accounts</h5>
        <p class="my-0 card-subtitle">Display content from social accounts on your site</p>
      </div>
      <div class="card-body pt-0">
        <div class="d-flex mb-4 align-items-center">
          <div class="flex-shrink-0">
            <img src="{{ asset('assets/img/icons/brands/facebook.png') }}" alt="facebook" class="me-4" height="36" />
          </div>
          <div class="flex-grow-1 row align-items-center me-n1">
            <div class="col-7 mb-sm-0 mb-2">
              <h6 class="mb-50">Facebook</h6>
              <span class="small">Not Connected</span>
            </div>
            <div class="col-5 text-end">
              <button class="btn btn-label-secondary btn-icon"><i class="icon-base ti tabler-link icon-22px"></i></button>
            </div>
          </div>
        </div>
        <div class="d-flex mb-4 align-items-center">
          <div class="flex-shrink-0">
            <img src="{{ asset('assets/img/icons/brands/twitter-' . $configData['theme'] . '.png') }}" alt="twitter" class="me-4" height="36" data-app-dark-img="icons/brands/twitter-dark.png" data-app-light-img="icons/brands/twitter-light.png" />
          </div>
          <div class="flex-grow-1 row align-items-center me-n1">
            <div class="col-7 mb-sm-0 mb-2">
              <h6 class="mb-1">Twitter</h6>
              <a href="{{ config('variables.twitterUrl') }}" class="small" target="_blank">{{ '@' . config('variables.creatorName') }}</a>
            </div>
            <div class="col-5 text-end">
              <button class="btn btn-label-danger btn-icon"><i class="icon-base ti tabler-trash icon-22px"></i></button>
            </div>
          </div>
        </div>
        <div class="d-flex mb-4 align-items-center">
          <div class="flex-shrink-0">
            <img src="{{ asset('assets/img/icons/brands/linkedin.png') }}" alt="linkedin" class="me-4" height="36" />
          </div>
          <div class="flex-grow-1 row align-items-center me-n1">
            <div class="col-7 mb-sm-0 mb-2">
              <h6 class="mb-1">linkedin</h6>
              <a href="{{ config('variables.instagramUrl') }}" class="small" target="_blank">{{ '@' . config('variables.creatorName') }}</a>
            </div>
            <div class="col-5 text-end">
              <button class="btn btn-label-danger btn-icon"><i class="icon-base ti tabler-trash icon-22px"></i></button>
            </div>
          </div>
        </div>
        <div class="d-flex mb-4 align-items-center">
          <div class="flex-shrink-0">
            <img src="{{ asset('assets/img/icons/brands/dribbble.png') }}" alt="dribbble" class="me-4" height="36" />
          </div>
          <div class="flex-grow-1 row align-items-center me-n1">
            <div class="col-7 mb-sm-0 mb-2">
              <h6 class="mb-50">Dribbble</h6>
              <span class="small">Not Connected</span>
            </div>
            <div class="col-5 text-end">
              <button class="btn btn-label-secondary btn-icon"><i class="icon-base ti tabler-link icon-22px"></i></button>
            </div>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <img src="{{ asset('assets/img/icons/brands/behance.png') }}" alt="behance" class="me-4" height="36" />
          </div>
          <div class="flex-grow-1 row align-items-center me-n1">
            <div class="col-7 mb-sm-0 mb-2">
              <h6 class="mb-50">Behance</h6>
              <span class="small">Not Connected</span>
            </div>
            <div class="col-5 text-end">
              <button class="btn btn-label-secondary btn-icon"><i class="icon-base ti tabler-link icon-22px"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /Social Accounts -->
</div>

<!-- Modals  -->
@include('_partials/_modals/modal-edit-user')
@include('_partials/_modals/modal-upgrade-plan')
<!-- /Modals -->
@endsection
