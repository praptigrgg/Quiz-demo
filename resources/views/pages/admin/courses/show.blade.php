@extends('layouts.app')

@section('title', $course->title)

@section('content')
<div class="container py-4">
    <h2>{{ $course->title }}</h2>

    <div class="card mt-3">
        <div class="card-body">
            {{-- Display course description with LaTeX --}}
            <div id="course-description">
                {!! $course->description !!}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Render MathJax after page loads
    document.addEventListener("DOMContentLoaded", function() {
        MathJax.typesetPromise();
    });
</script>
@endpush
