@extends('layouts/layoutMaster')

@section('title', 'Theme Settings')

@section('content')
<div class="row g-6">
  @include('content/settings/sidebar')

  <!-- Options -->
  <div class="col-12 col-lg-9 pt-6 pt-lg-0">
    <div class="tab-content p-0">
      <!-- Theme Tab -->
      <div class="tab-pane fade show active" id="theme" role="tabpanel">
        <form id="themeSettingsForm" action="{{ route('settings.theme.update') }}" method="post">
          @csrf
          <div class="card mb-6">
            <div class="card-header">
              <h5 class="card-title mb-0">Theme Options</h5>
            </div>
            <div class="card-body">
              <div class="row mb-6 g-6">
                <div class="col-12">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="use-default-colors" name="useDefaultColors" value="1" {{ ($setting['default_theme'] ?? '0') === '1' ? 'checked' : '' }} />
                    <label class="form-check-label" for="use-default-colors">
                      Use Default Colors
                    </label>
                    <small class="text-muted d-block mt-1">If enabled, the app will use default green colors without applying custom theme colors</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card mb-6">
            <div class="card-header">
              <h5 class="card-title mb-0">Button Colors</h5>
            </div>
            <div class="card-body">
              <div class="row mb-6 g-6">
                <div class="col-12 col-md-6">
                  <label class="form-label mb-1" for="button-color">Primary Color</label>
                  <div class="input-group">
                    <input type="color" class="form-control form-control-color" id="button-color" name="buttonColor" value="{{ $setting['theme_primary_color'] ?? '#16a34a' }}" />
                    <input type="text" class="form-control" id="button-color-text" value="{{ $setting['theme_primary_color'] ?? '#16a34a' }}" placeholder="#16a34a" />
                  </div>
                  <small class="text-muted">Used for: all bg-green-*, text-green-*, and border-green-* classes</small>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label mb-1" for="button-hover-color">Secondary Color</label>
                  <div class="input-group">
                    <input type="color" class="form-control form-control-color" id="button-hover-color" name="buttonHoverColor" value="{{ $setting['theme_secondary_color'] ?? '#15803d' }}" />
                    <input type="text" class="form-control" id="button-hover-color-text" value="{{ $setting['theme_secondary_color'] ?? '#15803d' }}" placeholder="#15803d" />
                  </div>
                  <small class="text-muted">Used for: all hover:bg-green-* and hover:text-green-* classes</small>
                </div>
              </div>
            </div>
          </div>

          <div class="card mb-6">
            <div class="card-header">
              <h5 class="card-title mb-0">Login Button Color</h5>
            </div>
            <div class="card-body">
              <div class="row mb-6 g-6">
                <div class="col-12 col-md-6">
                  <label class="form-label mb-1" for="button-login-color">Login Button Color</label>
                  <div class="input-group">
                    <input type="color" class="form-control form-control-color" id="button-login-color" name="buttonLoginColor" value="{{ $setting['theme_button_login'] ?? '#000000' }}" />
                    <input type="text" class="form-control" id="button-login-color-text" value="{{ $setting['theme_button_login'] ?? '#000000' }}" placeholder="#000000" />
                  </div>
                  <small class="text-muted">Used for: login form buttons (bg-black)</small>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-4">
            <button type="reset" class="btn btn-label-secondary">Discard</button>
            <button class="btn btn-primary" type="submit">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- /Options-->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Sync color picker with text input
  const colorInputs = document.querySelectorAll('input[type="color"]');
  colorInputs.forEach(input => {
    const textInput = document.getElementById(input.id + '-text');
    if (textInput) {
      input.addEventListener('input', function() {
        textInput.value = this.value;
      });
      textInput.addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
          input.value = this.value;
        }
      });
    }
  });
});
</script>
@endsection

