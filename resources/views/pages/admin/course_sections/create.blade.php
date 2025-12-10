@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-3">
            Create New Section
            <span class="text-danger">[{{ $course->title }}]</span>
        </h3>

        <div class="mb-4 d-flex gap-2">
            <a href="{{ route('admin.courses.sections.create', $course->id) }}" class="btn btn-success">
                <i class="bx bx-plus"></i> Add New Section
            </a>
            <a href="#" class="btn btn-danger">
                <i class="bx bx-list-ul"></i> Course Linked Quizzes
            </a>
        </div>

        <form action="{{ route('admin.courses.sections.store', $course->id) }}" method="POST">
            @csrf

            {{-- Section Title --}}
            <div class="mb-3">
                <label class="form-label text-success fw-bold">
                    Section Title <span class="text-danger">*</span>
                </label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                    placeholder="Introduction to Course" value="{{ old('title') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Order Position --}}
            <div class="mb-3">
                <label class="form-label text-success fw-bold">Order Position</label>
                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror"
                    value="{{ old('order', 0) }}" min="0">
                @error('order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Quiz Selector --}}
            <div class="mb-3">
                <label class="form-label text-success fw-bold">Select Quiz(es)</label>
                <select id="quizzes" name="quizzes[]" class="form-control select2" multiple
                    data-placeholder="Search and Select Quizzes">
                    @foreach ($allQuizzes as $quiz)
                        <option value="{{ $quiz->id }}" {{ in_array($quiz->id, old('quizzes', [])) ? 'selected' : '' }}>
                            {{ $quiz->title }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Selected quizzes will be listed below.</small>
            </div>



            <div class="mb-3">
                <label class="form-label">Course Description</label>
                <textarea name="description" id="description-editor">{{ old('description') }}</textarea>
            </div>





            <button type="submit" class="btn btn-success">Save Section</button>
        </form>
    </div>
@endsection


@push('scripts')
    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/4.22.1/full-all/ckeditor.js"></script>

    <!-- MathJax -->
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [
                    ['$', '$'],
                     ['$$', '$$'],
                    ['\\(', '\\)']
                ],
                displayMath: [

                    ['\\[', '\\]']
                ]
            },
            options: {
                skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code']
            }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

    <script>
        CKEDITOR.replace('description-editor', {
            height: 250,
            extraPlugins: 'mathjax',
            mathJaxLib: 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js'
        }).on('contentDom', function() {
            var editor = this;
            var editable = editor.editable();

            var timeoutId = null;

            editable.attachListener(editable, 'keyup', function() {
                if (timeoutId) clearTimeout(timeoutId);

                timeoutId = setTimeout(function() {
                    if (window.MathJax) {
                        MathJax.typesetPromise([editable.$]);
                    }
                }, 400);
            });

            if (window.MathJax) MathJax.typesetPromise([editable.$]);
        });
    </script>
@endpush
