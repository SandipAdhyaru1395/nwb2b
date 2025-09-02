@extends('layouts/layoutMaster')

@section('title', 'Product Edit')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/dropzone/dropzone.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/tagify/tagify.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/app-ecommerce-product-edit.js'])
    <script>
        $(document).ready(function() {
            $('#category').change(function() {
                var cat_id = $(this).val();

                $.ajax({
                    url: "{{ route('subcategory.list.by.category.ajax') }}",
                    type: "GET",
                    data: {
                        cat_id: cat_id,
                    },
                    success: function(sub_categories) {
                        $('#sub_category').empty();

                        if (sub_categories.length != 0) {
                            sub_categories.forEach(function(sub_category) {
                                $('#sub_category').append('<option value="' +
                                    sub_category.id + '">' + sub_category.name +
                                    '</option>');
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Add Product -->
        <form id="editProductForm" method="POST" action="{{ route('product.update') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $product->id }}">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Edit Product</h4>
                    <p class="mb-0">Orders placed across your store</p>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    @if ($product->is_published)
                        <div class="d-flex gap-4">
                            <button type="button" class="btn btn-label-secondary"
                                onclick="window.location.reload();">Discard</button>
                        </div>
                        <button type="submit" name="action" value="1" class="btn btn-success">Publish</button>
                        <button type="submit" name="action" value="0" class="btn btn-danger">Unpublish</button>
                    @else
                        <div class="d-flex gap-4">
                            <button type="button" class="btn btn-label-secondary"
                                onclick="window.location.reload();">Discard</button>
                            <button type="submit" name="action" value="0" class="btn btn-label-primary">Save
                                draft</button>
                        </div>

                        <button type="submit" name="action" value="1" class="btn btn-success">Publish</button>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- First column-->
                <div class="col-12 col-lg-8">
                    <!-- Product Information -->
                    <div class="card mb-6">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="card-tile mb-0">Product information</h5>
                            @if ($product->is_published)
                                <span class="badge bg-label-success">Published</span>
                            @else
                                <span class="badge bg-label-secondary">Draft</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="ecommerce-product-name">Name</label>
                                <input type="text" class="form-control" id="ecommerce-product-name"
                                    placeholder="Product title" name="productTitle" aria-label="Product title"
                                    value="{{ $product->name }}" autocomplete="off" />
                                @error('productTitle')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="row mb-6">
                                <div class="col form-control-validation"><label class="form-label"
                                        for="ecommerce-product-sku">SKU</label>
                                    <input type="text" class="form-control" id="ecommerce-product-sku" placeholder="SKU"
                                        name="productSku" aria-label="Product SKU" value="{{ $product->sku }}"
                                        autocomplete="off" />
                                    @error('productSku')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col"><label class="form-label"
                                        for="ecommerce-product-barcode">Barcode</label>
                                    <input type="text" class="form-control" id="ecommerce-product-barcode"
                                        placeholder="0123-4567" name="productBarcode" aria-label="Product barcode"
                                        value="{{ $product->barcode }}" autocomplete="off" />
                                    @error('productBarcode')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
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
                                    <div class="comment-editor border-0 pb-6" id="product-description">
                                        {!! $product->description !!}</div>
                                </div>
                                <input type="hidden" name="productDescription" id="productDescription"
                                    value="{{ $product->description }}">
                            </div>
                        </div>
                    </div>
                    <!-- /Product Information -->
                    <!-- Media -->
                    <div class="card mb-6 pt-5">
                        <img class="align-self-center" height="300px" width="500px"
                            src="{{ asset('storage/' . $product->image) }}" />
                        <div class="card-body">
                            <input type="file" name="productImage" id="productImage" hidden>
                            <div class="dropzone needsclick p-0" id="dropzone-basic">
                                <div class="dz-message needsclick">
                                    <p class="h4 needsclick pt-3 mb-2">Drag and drop your image here</p>
                                    <p class="h6 text-body-secondary d-block fw-normal mb-2">or</p>
                                    <span class="needsclick btn btn-sm btn-label-primary" id="btnBrowse">Browse
                                        image</span>
                                </div>
                            </div>
                            @error('productImage')
                                <span class="text-danger text-center mb-5" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- /Media -->
                </div>
                <!-- /Second column -->

                <!-- Second column -->
                <div class="col-12 col-lg-4">
                    <!-- Pricing Card -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pricing</h5>
                        </div>
                        <div class="card-body">
                            <!-- Base Price -->
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="ecommerce-product-price">Base Price</label>
                                <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                    class="form-control" id="ecommerce-product-price" placeholder="Price"
                                    name="productPrice" aria-label="Product price" value="{{ $product->price }}"
                                    autocomplete="off" />
                                @error('productPrice')
                                    <span class="text-danger text-center mb-5" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <!-- Discounted Price -->
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="ecommerce-product-discount-price">Discounted Price</label>
                                <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                    class="form-control" id="ecommerce-product-discount-price"
                                    placeholder="Discounted Price" name="productDiscountedPrice"
                                    aria-label="Product discounted price" value="{{ $product->discounted_price }}"
                                    autocomplete="off" />
                                @error('productDiscountedPrice')
                                    <span class="text-danger text-center mb-5" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /Pricing Card -->
                    <!-- Organize Card -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Organize</h5>
                        </div>
                        <div class="card-body">

                            <!-- Category -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="mb-6 col ecommerce-select2-dropdown form-control-validation">
                                    <label class="form-label mb-1" for="category-org">
                                        <span>Category</span>
                                    </label>
                                    <select id="category" name="productCategory" class="select2 form-select"
                                        data-placeholder="Select Category">
                                        <option value="" selected>Select Category</option>
                                        @forelse($categories as $category)
                                            <option value="{{ $category->id }}" @selected($product->category_id == $category->id)>
                                                {{ $category->name }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    @error('productCategory')
                                        <span class="text-danger text-center mb-5" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <!-- Sub Category -->
                            <div class="mb-6 col ecommerce-select2-dropdown form-control-validation">
                                <label class="form-label mb-1" for="vendor"> Sub Category </label>
                                <select id="sub_category" name="productSubCategory" class="select2 form-select"
                                    data-placeholder="Select Subcategory">
                                    <option value="" selected>Select Subcategory</option>
                                    @forelse($sub_categories as $subCategory)
                                        <option value="{{ $subCategory->id }}" @selected($product->sub_category_id == $subCategory->id)>
                                            {{ $subCategory->name }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('productSubCategory')
                                    <span class="text-danger text-center mb-5" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <!-- Tags -->
                            <div>
                                <label for="ecommerce-product-tags" class="form-label mb-1">Tags</label>
                                <input id="ecommerce-product-tags" class="form-control" name="productTags"
                                    value="{{ $product->tags }}" aria-label="Product Tags" />
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
