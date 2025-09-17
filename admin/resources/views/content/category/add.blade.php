@extends('layouts/layoutMaster')

@section('title', 'Add Category')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/quill/quill.js','resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js',
        'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/category-add.js'])
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Add Product -->
        <form id="addCategoryForm" method="POST" action="{{ route('category.create') }}" enctype="multipart/form-data">
            @csrf

            <div
                style="background: var(--bs-body-bg);" class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Add category</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('category.list') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- First column-->
                <div class="col-12">
                    <!-- Product Information -->
                    <div class="card mb-6">
                        <div class="card-body">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="categoryName">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="categoryName" placeholder="Category name"
                                    name="categoryName" aria-label="Category name" value="{{ old('categoryName') }}"
                                    autocomplete="off" />
                                @error('categoryName')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="row mb-6">
                                <div class="col form-control-validation"><label class="form-label"
                                        for="parent_category">Parent Category</label>
                                    <select class="select2" id="parent_category" name="parentCategory">
                                        <option value="">Select</option>
                                        @forelse($categories as $category)
                                            @include('_partials.parent_category_option', [
                                                'category' => $category,
                                                'prefix' => '',
                                            ])
                                        @empty
                                        @endforelse
                                    </select>

                                </div>
                            </div>
                            <!-- Description -->
                            <div>
                                <label class="mb-1">Description (Optional)</label>
                                <div class="form-control p-0">
                                    <div class="comment-toolbar border-0 border-bottom">
                                        <div class="d-flex justify-content-start">
                                            <span class="ql-formats me-0">
                                                <button class="ql-bold"></button>
                                                <button class="ql-italic"></button>
                                                <button class="ql-underline"></button>
                                                <button class="ql-list" value="ordered"></button>
                                                <button class="ql-list" value="bullet"></button>
                                                <button class="ql-link"></button>
                                                <button class="ql-image"></button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="comment-editor border-0 pb-6" id="category-description">
                                    </div>
                                </div>
                                <input type="hidden" name="categoryDescription" id="category-description-hidden"
                                    value="">
                            </div>
                            <div class="mt-5">
                                <label class="form-label">Status</label>
                                <select class="select2" id="categoryStatus" name="categoryStatus">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                @error('categoryStatus')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Second column -->

            </div>
        </form>
    </div>

@endsection
