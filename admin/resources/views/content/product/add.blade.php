@extends('layouts/layoutMaster')

@section('title', 'Product Add')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/quill/quill.js','resources/assets/vendor/libs/select2/select2.js','resources/assets/vendor/libs/dropzone/dropzone.js','resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/product-add.js'])
<script>
    $(document).ready(function() {
        
        $('#stepPlus').click(function() {
            
            var step = $('#step').val();

            if(step > 0) {
                step = parseInt(step) + 1;
                $('#step').val(step);
            }else{
                $('#step').val(1);
            }
        });

        $('#stepMinus').click(function() {
             var step = $('#step').val();

            if(step > 1) {
                step = parseInt(step) - 1;
                $('#step').val(step);
            }else{
                $('#step').val(1);
            }
        });
        $('#step').on('paste', function(e) {
            e.preventDefault();
        });
    });
</script>
@endsection

@section('content')
<div class="app-ecommerce">
    <!-- Add Product -->
    <form id="addProductForm" method="POST" action="{{ route('product.create') }}" enctype="multipart/form-data">
        @csrf

        <div
                style="background: var(--bs-body-bg);" class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">Add a new Product</h4>
                <p class="mb-0">Orders placed across your store</p>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-4">
                <div class="d-flex gap-4">
                    <a href="{{ route('product.list') }}" class="btn btn-secondary">Cancel</a>
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
                            <div class="col"><label class="form-label"
                                    for="ecommerce-product-sku">SKU</label>
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
                        <div class="row">
                            <!-- Base Price -->
                            <div class="col-lg-4 mb-5 form-control-validation">
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
                            <div class="col-lg-4 mb-5 form-control-validation">
                                <label class="form-label" for="cost-price">Purchase Price </label>
                                <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control"
                                    id="cost-price" placeholder="Price" name="costPrice" aria-label="Cost price"
                                    value="{{ old('costPrice') }}" autocomplete="off" />
                            </div>
                            <!-- Wallet Credit -->
                            <div class="col-lg-4 mb-5 form-control-validation">
                                <label class="form-label" for="wallet-credit">Wallet Credit</label>
                                <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control"
                                    id="wallet-credit" placeholder="Credit" name="walletCredit" aria-label="Wallet Credit"
                                    value="{{ old('walletCredit') }}" autocomplete="off" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-5 form-control-validation"><label class="form-label"
                                    for="quantity">Quantity</label>
                                <input type="text" class="form-control" id="quantity" placeholder="Enter quantity"
                                    name="quantity" onkeypress="return /^[0-9]+$/.test(event.key)"
                                    aria-label="Product Quantity" value="{{ old('quantity') }}" autocomplete="off" />
                            </div>
                            <div class="col-md-4 mb-5 form-control-validation">
    <label class="form-label" for="vat_method_id">VAT Method</label>
    <select name="vat_method_id" id="vat_method_id" class="form-select" required>
        <option value="">-- Select VAT Method --</option>
        @foreach($vatMethods as $vat)
            <option value="{{ $vat->id }}" 
                data-type="{{ $vat->type }}" 
                data-name="{{ $vat->name }}"
                data-amount="{{ $vat->amount }}">
                {{ $vat->name }} ({{ $vat->type == 'Percentage' ? $vat->amount.'%' : $currencySymbol.number_format($vat->amount,2) }})
            </option>
        @endforeach
    </select>
    <input type="hidden" name="vat_method_name" id="vat_method_name">
    <input type="hidden" name="vat_method_type" id="vat_method_type">
    <input type="hidden" name="vat_amount" id="vat_amount">
    <div class="form-text mt-1">Calculated VAT: <span id="vat_amount_display">0.00</span></div>
    {{-- @error('vat_method_id') <span class="text-danger">{{ $message }}</span> @enderror --}}
</div>
                            <div class="col-md-4 col-8 mx-auto mb-5 form-control-validation"><label class="form-label"
                                     for="step_quantity" class="form-label">Step Quantity</label>
                                <div class="position-relative d-flex gap-2 align-items-center w-75">
                                    <button style="width:25px; left:10;" class="left-0 position-absolute btn btn-sm btn-danger h-75" type="button" id="stepMinus">
                                        <i class="flex-shrink-0 ti tabler-minus"></i>
                                    </button>
                                    <input type="text" name="step" id="step" onkeypress="return /^[0-9]+$/.test(event.key)" class="form-control text-center" value="1" autocomplete="off">
                                    <button style="width:25px; right:10;" class="position-absolute btn btn-sm btn-success h-75" type="button" id="stepPlus">
                                        <i class="flex-shrink-0 ti tabler-plus"></i>
                                    </button>
                                </div>
                                @error('step')
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
                <!-- Organize Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Product Label</h5>
                    </div>
                    <div class="card-body">
                        <!-- Status -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="mb-6 col ecommerce-select2-dropdown form-control-validation">
                                <label class="form-label mb-5" for="productStatus">
                                    <span>Status <span class="text-danger">*</span></span>
                                </label>
                                <select class="form-select select2" name="productStatus" id="productStatus">
                                    <option value="1" @selected(old('productStatus')=='1' )>Active</option>
                                    <option value="0" @selected(old('productStatus')=='0' )>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-6 form-control-validation">
                            <label class="form-label" for="brand_id">Brands <span
                                    class="text-danger">*</span></label>
                            <select class="form-control select2" name="brands[]" multiple>
                                @forelse ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(old('brand_id')==$brand->id)>
                                    {{ $brand->name }}
                                </option>
                                @empty
                                @endforelse
                            </select>
                            @error('brands')
                            <span class="text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
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

@push('scripts')
<script>
var vatMethodsJS = @json($vatMethods);
var currencySymbol = @json($currencySymbol);
function updateVatAmount() {
    var price = parseFloat(document.getElementById('ecommerce-product-price').value) || 0;
    var select = document.getElementById('vat_method_id');
    var selected = select.options[select.selectedIndex];
    if(!selected || !selected.value) {
        document.getElementById('vat_amount').value = '';
        document.getElementById('vat_method_type').value = '';
        document.getElementById('vat_method_name').value = '';
        document.getElementById('vat_amount_display').innerText = '0.00';
        return;
    }
    var type = selected.getAttribute('data-type');
    var name = selected.getAttribute('data-name');
    var amount = parseFloat(selected.getAttribute('data-amount'));
    var vat = 0;
    if(type === 'Percentage') {
        vat = price * amount / 100;
    } else {
        vat = amount;
    }
    document.getElementById('vat_amount').value = vat.toFixed(2);
    document.getElementById('vat_method_type').value = type;
    document.getElementById('vat_method_name').value = name;
    document.getElementById('vat_amount_display').innerText = vat.toFixed(2)+(type==='Percentage'?'%':' '+currencySymbol);
}
document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('vat_method_id').addEventListener('change', updateVatAmount);
    document.getElementById('ecommerce-product-price').addEventListener('input', updateVatAmount);
    updateVatAmount(); // Initial, so if prefilled or browser fill is used, it shows right away
});
</script>
@endpush