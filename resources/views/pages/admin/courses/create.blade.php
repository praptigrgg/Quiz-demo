@extends('layouts.app')

@section('title', 'Add Course')

@section('content')
<div class="container">
    <h2>Add Course</h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Course Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Thumbnail</label>
            <input type="file" name="thumbnail" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Demo Video</label>
            <input type="url" name="demo_video" class="form-control" value="{{ old('demo_video') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Course Description</label>
            <textarea name="description" class="form-control" id="summernote">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Course</button>
    </form>

    <div class="mt-3">
        <h5>Live Preview</h5>
        <div id="latex-preview"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#summernote').summernote({
        placeholder: 'Enter description here...',
        tabsize: 2,
        height: 300,
        callbacks: {
            onChange: function(contents) {
                $('#latex-preview').html(contents);
                MathJax.typesetPromise(); // render math
            },
            onInit: function() {
                $('#latex-preview').html($('#summernote').val());
                MathJax.typesetPromise();
            }
        }
    });
});
</script>
@endpush
