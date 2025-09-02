@extends('layouts/layoutMaster')

@section('title', 'Product Sub Category')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    'resources/assets/vendor/libs/quill/typography.scss',
    'resources/assets/vendor/libs/quill/katex.scss',
    'resources/assets/vendor/libs/quill/editor.scss'
  ])
@endsection

@section('page-style')
  @vite('resources/assets/vendor/scss/pages/app-ecommerce.scss')
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/quill/katex.js',
    'resources/assets/vendor/libs/quill/quill.js'
  ])
@endsection

@section('page-script')
  @vite('resources/assets/js/app-ecommerce-sub-category-list.js')
  <script>
    $(document).ready(function () {

      $('#offcanvasEditSubCategory').on('show.bs.offcanvas', function (e) {
      
        var id = $(e.relatedTarget).data('id');
        
        $.ajax({
          url: "{{ route('subcategory.show.ajax') }}",
          type: 'GET',
          data: { id: id },
          success: function (response) {
            
             $('#updateSubCategoryForm').find('#id').val(response.id);
            $('#updateSubCategoryForm').find('#edit-sub-category-title').val(response.name);
            
            // Initialize Quill editor if not available and set content
            if (typeof window.quillEdit === 'undefined' || !window.quillEdit) {
              if (typeof initializeQuillEdit === 'function') {
                window.quillEdit = initializeQuillEdit();
              }
            }
            
            if (window.quillEdit) {
              window.quillEdit.root.innerHTML = response.description;
            }

            $('#updateSubCategoryForm').find('#edit-sub-category-select').val(response.category.id).trigger('change');
            
            $('#updateSubCategoryForm').find('#edit-sub-category-status').val(response.status).trigger('change');
          }
        });
      });
    });
  </script>
@endsection

@section('content')
  <div class="app-ecommerce-category">
    <!-- Category List Table -->
    <div class="card">
      <div class="card-datatable">
        <table class="datatables-sub-category-list table">
          <thead>
            <tr>
              <th></th>
              <th></th>
              <th>Sub Category</th>
              <th>Category</th>
              <th>Status</th>
              <th class="text-lg-center">Actions</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
    @include('_partials/_offcanvas/offcanvas-add-sub-category')
    @include('_partials/_offcanvas/offcanvas-edit-sub-category')
  </div>
@endsection