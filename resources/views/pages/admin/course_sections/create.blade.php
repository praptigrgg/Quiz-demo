@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">
        Create New Section <span class="text-danger">[{{ $course->title }}]</span>
    </h3>

    <div class="mb-4">
        <a href="{{ route('admin.courses.sections.create', $course->id) }}" class="btn btn-success">
            <i class="bx bx-plus"></i> Add New Section
        </a>
        <a href="#" class="btn btn-danger">
            <i class="bx bx-list-ul"></i> Course Linked Quizzes
        </a>
    </div>

    <form action="{{ route('admin.courses.sections.store', $course->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="title" class="form-label text-success fw-bold">Section Title <span class="text-danger">*</span></label>
            <input type="text"
                   class="form-control @error('title') is-invalid @enderror"
                   id="title"
                   name="title"
                   placeholder="Introduction to Course"
                   value="{{ old('title') }}"
                   required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="order" class="form-label text-success fw-bold">Order Position</label>
            <input type="number"
                   class="form-control @error('order') is-invalid @enderror"
                   id="order"
                   name="order"
                   value="{{ old('order', 0) }}"
                   min="0">
            @error('order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="quizzes" class="form-label text-success fw-bold">Select Quiz(es)</label>
            <select id="quizzes"
                    name="quizzes[]"
                    class="form-control select2"
                    multiple="multiple"
                    data-placeholder="Search and Select Quizzes"
                    style="width: 100%;">
                @foreach($allQuizzes as $quiz)
                    <option value="{{ $quiz->id }}" {{ in_array($quiz->id, old('quizzes', [])) ? 'selected' : '' }}>
                        {{ $quiz->title }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Selected quizzes will be listed below.</small>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label text-success fw-bold">Description</label>
            <textarea id="description" name="description" class="form-control" rows="6" placeholder="Enter your course section description...">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">
            Save Section
        </button>
    </form>
</div>
@endsection

@section('scripts')
<!-- Include Select2 JS & CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#quizzes').select2({
        placeholder: "Search and Select Quizzes",
        allowClear: true
    });

});
</script>
@endsection
