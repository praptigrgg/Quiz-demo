@extends('layouts.app')

@section('content')

<style>
    .certificate-preview-wrapper {
        background: #f3f3f3;
        padding: 2rem 0;
    }

    .certificate-preview-frame {
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: none;
        width: 100%;
        min-height: 80vh;  /* adjust height as you like */
    }
</style>

<div class="container py-4">
    <h2 class="mb-3">Certificate Preview</h2>

    {{-- Alert messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Controls --}}
    <div class="mb-3 d-flex gap-2">
        <a href="{{ route('admin.certificates.edit') }}" class="btn btn-outline-secondary">
            ← Back & Edit
        </a>

        <form action="{{ route('admin.certificates.approve') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary">
                Save PDF
            </button>
        </form>
    </div>

    {{-- 👇 Actual PDF rendered by browser PDF viewer --}}
    <div class="certificate-preview-wrapper">
        <iframe
            src="{{ route('admin.certificates.preview_pdf') }}"
            class="certificate-preview-frame">
        </iframe>
    </div>
</div>

@endsection
