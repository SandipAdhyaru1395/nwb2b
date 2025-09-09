@extends('layouts/layoutMaster')

@section('title', 'Add Collection')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/dropzone/dropzone.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/tagify/tagify.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/app-ecommerce-collection-add.js'])
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Add Collection -->
        <form id="addCollectionForm" method="POST" action="{{ route('collection.create') }}" enctype="multipart/form-data">
            @csrf

            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Add a new Collection</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('collection-list') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- First column-->
                <div class="col-12 col-lg-8">
                    <!-- Collection Information -->
                    <div class="card mb-6">
                        <div class="card-body">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="collection-name">Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="collection-name"
                                    placeholder="Collection title" name="collectionTitle" aria-label="Collection title"
                                    value="{{ old('collectionTitle') }}" autocomplete="off" />
                                @error('collectionTitle')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /Collection Information -->
                    <!-- Media -->
                    <div class="card mb-6">
                        <div class="card-body form-control-validation">
                            <input type="file" name="collectionImage" id="collectionImage" hidden>
                            <div class="dropzone needsclick p-0" id="dropzone-basic">
                                <div class="dz-message needsclick">
                                    <p class="h4 needsclick pt-3 mb-2">Drag and drop your image here</p>
                                    <p class="h6 text-body-secondary d-block fw-normal mb-2">or</p>
                                    <span class="needsclick btn btn-sm btn-label-primary" id="btnBrowse">Browse
                                        image</span>
                                </div>
                            </div>
                        </div>
                        @error('collectionImage')
                            <span class="text-danger text-center mb-5" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <!-- /Media -->
                </div>
                <!-- /Second column -->

                <!-- Second column -->
                <div class="col-12 col-lg-4">
                    <!-- Organize Card -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Organize</h5>
                        </div>
                        <div class="card-body">
                             <!-- IS NEW -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check mb-6 col ecommerce-select2-dropdown form-control-validation">
                                    <input type="checkbox" class="form-check-input" name="isNew" id="isNew" @checked(old('isNew') == 'on')>
                                    <label class="form-check-label mb-5" for="isNew">
                                        New
                                    </label>
                                </div>
                                <div class="form-check mb-6 col ecommerce-select2-dropdown form-control-validation">    
                                    <input type="checkbox" class="form-check-input" name="isSale" id="isSale" @checked(old('isSale') == 'on')>
                                    <label class="form-check-label mb-5" for="isSale">
                                        Sale
                                    </label>
                                </div>
                                <div class="form-check mb-6 col ecommerce-select2-dropdown form-control-validation">    
                                    <input type="checkbox" class="form-check-input" name="isHot" id="isHot" @checked(old('isHot') == 'on')>
                                    <label class="form-check-label mb-5" for="isHot">
                                        Hot
                                    </label>
                                </div>
                            </div>
                            <!-- Status -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="mb-6 col ecommerce-select2-dropdown form-control-validation">
                                    <label class="form-label mb-5" for="collectionStatus">
                                        <span>Status <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-select select2" name="collectionStatus" id="collectionStatus">
                                        <option value="1" @selected(old('collectionStatus') == '1')>Active</option> 
                                        <option value="0" @selected(old('collectionStatus') == '0')>Inactive</option>
                                    </select>                                   
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="mb-6 col ecommerce-select2-dropdown form-control-validation">
                                    <label class="form-label mb-5" for="brand_id">
                                        <span>Brand <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-select select2" name="brand_id" id="brand_id">
                                            <option value="" selected>Select</option>
                                        @forelse($brands as $brand)
                                            <option value="{{$brand->id}}" @selected(old('brand_id') == $brand->id)>{{$brand->name}}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    @error('brand_id')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror                                   
                                </div>
                            </div>
                            <!-- Category -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="mb-6 col ecommerce-select2-dropdown form-control-validation">
                                    <label class="form-label mb-5" for="category-org">
                                        <span>Category <span class="text-danger">*</span></span>
                                    </label>
                                    <ul class="list-group">
                                        @foreach ($categories as $category)
                                            @include('_partials.category_checkbox', ['category' => $category])
                                        @endforeach
                                    </ul>

                                    @error('categories')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <!-- Tags -->
                            <div>
                                <label for="collection-tags" class="form-label mb-1">Tags</label>
                                <input id="collection-tags" class="form-control" name="collectionTags"
                                    aria-label="Collection Tags" value="" />
                            </div>
                        </div>
                    </div>
                    <!-- /Organize Card -->
                </div>
                <!-- /Second column -->
            </div>
        </form>
    </div>

@endsection