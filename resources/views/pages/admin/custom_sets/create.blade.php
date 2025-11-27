@extends('layouts.app')

@section('title', 'Create New Custom Set')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="mb-1">Custom Set Management</h4>
        <p class="mb-4">Create a new custom set with questions and answers</p>

        <div class="row">
            <div class="col-lg-12 mb-4 order-0">
                <div class="card p-3">
                    <div class="d-flex card-header align-items-center justify-content-between">
                        <div class="col-md-6">
                            <h4 class="card-title text-primary">Create New Custom Set</h4>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('admin.custom_sets.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="bx bx-reset"></i> Reset
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Validation --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        {{-- Custom Set Form --}}
                        <form action="{{ route('admin.custom_sets.store') }}" method="POST" id="customSetForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                {{-- LEFT SIDE --}}
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customSetTitle" class="form-label">Custom Set Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="customSetTitle" name="title" required placeholder="Enter custom set title">
                                    </div>

                                    <div class="mb-3">
                                        <label for="customSetDescription" class="form-label">Description</label>
                                        <textarea id="customSetDescription" name="description" class="form-control"></textarea>
                                    </div>


                                    {{-- Pricing Type --}}
                                    <div class="mb-3">
                                        <label class="form-label">Pricing Type</label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="pricingType" id="pricing_free" value="free" checked>
                                                <label class="form-check-label" for="pricing_free">Free</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="pricingType" id="pricing_paid" value="paid">
                                                <label class="form-check-label" for="pricing_paid">Paid</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="pricingFields" style="display:none;">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="normal_price" class="form-label">Normal Price</label>
                                                <input type="number" class="form-control" name="normal_price" id="normal_price" step="0.01">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="discount_price" class="form-label">Discount Price</label>
                                                <input type="number" class="form-control" name="discount_price" id="discount_price" step="0.01">
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                {{-- RIGHT SIDE --}}
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="publishStatus" name="is_publish" value="1">
                                        <label class="form-check-label" for="publishStatus">Publish Immediately</label>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Save Custom Set</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Dependencies --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pricing fields toggle
    const pricingRadios = document.querySelectorAll('input[name="pricingType"]');
    const pricingFields = document.getElementById('pricingFields');

    function togglePricingFields() {
        const selected = document.querySelector('input[name="pricingType"]:checked').value;
        pricingFields.style.display = selected === 'paid' ? 'block' : 'none';
        document.querySelectorAll('#pricingFields input').forEach(i => { i.disabled = selected !== 'paid'; });
    }
    pricingRadios.forEach(r => r.addEventListener('change', togglePricingFields));
    togglePricingFields();

    // Summernote init
    $('#customSetDescription').summernote({
        placeholder: 'Enter custom set description...',
        height: 200,
        tabsize: 2
    });
});
</script>
@endsection
