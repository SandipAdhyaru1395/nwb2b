@extends('layouts/layoutMaster')

@section('title', 'Product Add')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/dropzone/dropzone.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/tagify/tagify.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/app-ecommerce-product-add.js'])
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Add Product -->
        <form id="addProductForm" method="POST" action="{{ route('product.create') }}" enctype="multipart/form-data">
            @csrf

            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Add a new Product</h4>
                    <p class="mb-0">Orders placed across your store</p>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('product-list') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- First column-->
                <div class="col-12 col-lg-8">
                    <!-- Product Information -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-tile mb-0">Product information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="collection_id">Collection <span
                                        class="text-danger">*</span></label>
                                <select class="form-select select2" name="collection_id" id="collection_id">
                                    <option value="" selected>Select</option>
                                    @forelse ($collections as $collection)
                                        <option value="{{ $collection->id }}" @selected(old('collection_id') == $collection->id)>
                                            {{ $collection->name }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('collection_id')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="ecommerce-product-name">Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ecommerce-product-name"
                                    placeholder="Product title" name="productTitle" aria-label="Product title"
                                    value="{{ old('productTitle') }}" autocomplete="off" />
                                @error('productTitle')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="row mb-6">
                                <div class="col form-control-validation"><label class="form-label"
                                        for="ecommerce-product-sku">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ecommerce-product-sku" placeholder="SKU"
                                        name="productSku" aria-label="Product SKU" value="{{ old('productSku') }}"
                                        autocomplete="off" />
                                    @error('productSku')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                            </div>
                            <div class="row mb-6">
                                <div class="col form-control-validation"><label class="form-label"
                                        for="quantity">Quantity</label>
                                    <input type="text" class="form-control" id="quantity" placeholder="Enter quantity"
                                        name="quantity" onkeypress="return /^[0-9]+$/.test(event.key)"
                                        aria-label="Product Quantity" value="{{ old('quantity') }}" autocomplete="off" />
                                </div>
                                <div class="col form-control-validation"><label class="form-label"
                                        for="min_order_quantity">Min Order Quantity</label>
                                    <input type="text" class="form-control" id="min_order_quantity"
                                        placeholder="Enter minimum order quantity" name="min_order_quantity"
                                        onkeypress="return /^[0-9]+$/.test(event.key)"
                                        value="{{ old('min_order_quantity') }}" autocomplete="off" />
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
                                        {!! old('productDescription') !!}
                                    </div>
                                </div>
                                <input type="hidden" name="productDescription" id="productDescription"
                                    value="{{ old('productDescription') }}">
                            </div>
                        </div>
                    </div>
                    <!-- /Product Information -->
                    <!-- Media -->
                    <div class="card mb-6">
                        <div class="card-body form-control-validation">
                            <input type="file" name="productImage" id="productImage" hidden>
                            <div class="dropzone needsclick p-0" id="dropzone-basic">
                                <div class="dz-message needsclick">
                                    <p class="h4 needsclick pt-3 mb-2">Drag and drop your image here</p>
                                    <p class="h6 text-body-secondary d-block fw-normal mb-2">or</p>
                                    <span class="needsclick btn btn-sm btn-label-primary" id="btnBrowse">Browse
                                        image</span>
                                </div>
                            </div>
                        </div>
                        @error('productImage')
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
                    <!-- Pricing Card -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pricing</h5>
                        </div>
                        <div class="card-body">
                            <!-- Base Price -->
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="ecommerce-product-price">Price <span
                                        class="text-danger">*</span></label>
                                <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control"
                                    id="ecommerce-product-price" placeholder="Price" name="productPrice"
                                    aria-label="Product price" value="{{ old('productPrice') }}" autocomplete="off" />
                                @error('productPrice')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <!-- Cost Price -->
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="cost-price">Cost Price </label>
                                <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control"
                                    id="cost-price" placeholder="Price" name="costPrice" aria-label="Cost price"
                                    value="{{ old('costPrice') }}" autocomplete="off" />
                            </div>
                            <!-- Wallet Credit -->
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="wallet-credit">Wallet Credit</label>
                                <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control"
                                    id="wallet-credit" placeholder="Credit" name="walletCredit" aria-label="Wallet Credit"
                                    value="{{ old('walletCredit') }}" autocomplete="off" />
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
                            <!-- Status -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="mb-6 col ecommerce-select2-dropdown form-control-validation">
                                    <label class="form-label mb-5" for="productStatus">
                                        <span>Status <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-select select2" name="productStatus" id="productStatus">
                                        <option value="1" @selected(old('productStatus') == '1')>Active</option>
                                        <option value="0" @selected(old('productStatus') == '0')>Inactive</option>
                                    </select>
                                </div>
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