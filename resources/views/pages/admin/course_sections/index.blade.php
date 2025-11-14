@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="mb-1">Course Sections Management</h4>
        <p class="mb-4">Manage courses, their sections, and lessons here.</p>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-custom-icon">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <i class="breadcrumb-icon icon-base bx bx-chevron-right align-middle"></i>
                </li>
                <li class="breadcrumb-item">
                    <a href="javascript:void(0);">Course Management</a>
                    <i class="breadcrumb-icon icon-base bx bx-chevron-right align-middle"></i>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.courses.index') }}">Course Lists</a>
                    <i class="breadcrumb-icon icon-base bx bx-chevron-right align-middle"></i>
                </li>
                <li class="breadcrumb-item active text-primary">Course Section</li>
            </ol>
        </nav>

        <!-- Buttons -->
        <div class="mb-3">
            <button type="button" class="btn btn-primary" id="showCreateForm">
                <i class="bx bx-plus me-1"></i> Add New Section
            </button>
            <button type="button" class="btn btn-secondary ms-2" data-bs-toggle="modal" data-bs-target="#courseLinkedQuizzesModal">
                <i class="bx bx-list-ul me-1"></i> Course Linked Quizzes
            </button>
        </div>

        <!-- Add New Section Form -->
        <div class="card mt-3" id="createSectionCard" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title text-primary mb-0">Create New Section</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.courses.sections.store', $course->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="course_id" value="{{ $course->id }}">

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Section Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Select Quiz(es)</label>
                                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#selectQuizModal">
                                    <i class="bx bx-search me-1"></i> Search and Select Quizzes
                                </button>
                                <small class="text-muted">Selected quizzes will be listed below.</small>
                            </div>
                            <div class="selected-quizzes mt-2" id="selectedQuizzes"></div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Order Position</label>
                                <input type="number" class="form-control" name="order" value="0">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </form>
            </div>
        </div>

        <!-- Sections List -->
        <div class="card mt-4" id="sectionsListCard">
            <div class="card-header">
                <h5 class="card-title text-primary mb-0">Course Sections</h5>
            </div>

            <div class="card-body">
                @forelse($sections as $section)
                <div class="section-item mb-4 border-bottom pb-3">

                    <!-- Section Header -->
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $loop->iteration }}. {{ $section->title }}</h6>
                        <div class="btn-group">
                            <!-- Add Lesson -->
                            <a href="{{ route('admin.courses.lessons.create', $section->id) }}">
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </a>

                            <!-- Edit -->
                            <a href="{{ route('admin.courses.sections.edit', [$course->id, $section->id]) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-edit"></i>
                            </a>

                            <!-- Delete -->
                            <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteCourseSectionConfirmationModal"
                                data-delete-url="{{ route('admin.courses.sections.destroy', [$course->id, $section->id]) }}">
                                <i class="bx bx-trash"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Lessons -->
                    <div class="lessons-container mt-3">
                        @foreach($section->lessons ?? [] as $lesson)
                        <div class="lesson-item mb-2 ps-4">

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span>{{ $loop->iteration }}.</span>
                                    <span>{{ $lesson->title }}</span>
                                    <small class="text-muted ms-2">{{ $lesson->duration }} mins</small>
                                </div>

                                <div class="btn-group">
                                    <!-- Edit Lesson -->
                                    <a href="{{ route('admin.courses.lessons.edit', [$section->id, $lesson->id]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bx bx-edit"></i>
                                    </a>

                                    <!-- Delete Lesson -->
                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteLessonConfirmationModal"
                                        data-section-id="{{ $section->id }}"
                                        data-lesson-id="{{ $lesson->id }}">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </div>
                            </div>

                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <p class="text-muted">No sections added yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Toggle Form JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('showCreateForm');
    const card = document.getElementById('createSectionCard');

    btn.addEventListener('click', function () {
        const hidden = card.style.display === 'none';
        card.style.display = hidden ? 'block' : 'none';

        btn.innerHTML = hidden
            ? '<i class="bx bx-x me-1"></i> Cancel Add Section'
            : '<i class="bx bx-plus me-1"></i> Add New Section';

        if (hidden) card.scrollIntoView({ behavior: 'smooth' });
    });
});
</script>
@endsection
