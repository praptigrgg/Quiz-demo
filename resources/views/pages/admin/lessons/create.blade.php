@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="mb-1">Courses Management</h4>
        <p class="mb-4">Add a new lesson, including sections and resources, here.</p>

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
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.courses.sections.index', $section->course_id) }}">Course Section</a>
                    <i class="breadcrumb-icon icon-base bx bx-chevron-right align-middle"></i>
                </li>
                <li class="breadcrumb-item active text-primary">Add Lesson</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-12 mb-4 order-0">
                <div class="card p-4 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between bg-light rounded">
                        <h4 class="card-title text-primary mb-0">Add Lesson <span class="text-secondary">[{{ $section->title }}]</span></h4>
                    </div>

                    <div class="card-body">
                        <form id="lessonForm" method="POST" enctype="multipart/form-data"
                              action="{{ route('admin.courses.lessons.store', $section->id) }}">
                            @csrf
                            <input type="hidden" name="section_id" value="{{ $section->id }}">
                            <input type="hidden" name="course_id" value="{{ $section->course_id }}">

                            <!-- Basic Lesson Info -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lessonTitle" class="form-label">Lesson Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lessonTitle" name="title" placeholder="Enter lesson title" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="selectQuizButton" class="form-label">Select Quiz(es)</label>
                                        <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#selectQuizModal">
                                            <i class="bx bx-search me-1"></i> Search and Select Quizzes
                                        </button>
                                        <small class="text-muted">Selected quizzes will be listed below.</small>
                                    </div>
                                    <div class="selected-quizzes mt-2" id="selectedQuizzes"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="duration" class="form-label">Lesson Duration (minutes) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="duration" name="duration" min="1" value="" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sequenceNumber" class="form-label">Sequence Number <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="sequenceNumber" name="order" min="1" value="" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="lessonDescription" class="form-label">Lesson Description</label>
                                        <textarea class="form-control summernote" id="lessonDescription" name="description"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Quiz Selection Modal -->
                            <div class="modal fade" id="selectQuizModal" tabindex="-1" aria-labelledby="selectQuizModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="selectQuizModalLabel">Search and Select Quizzes</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="quizSearch" class="form-label">Search Quizzes</label>
                                                <input type="text" class="form-control" id="quizSearch" placeholder="Search by quiz title...">
                                            </div>
                                            <div id="quizList" class="list-group" style="max-height: 300px; overflow-y: auto;">
                                                <!-- Quiz list populated dynamically via JS -->
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-primary" id="confirmQuizSelection">Confirm Selection</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4" style="border-color: var(--primary);">

                            <!-- Resources Section -->
                            <div class="resources-section mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0 text-primary">Lesson Resources</h5>
                                    <button type="button" class="btn btn-primary btn-sm" id="addResource">
                                        <i class="bx bx-plus me-1"></i> Add Resource
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="text-primary text-center">
                                            <tr>
                                                <th>Resource Type</th>
                                                <th>Details</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="resourcesContainer">
                                            <!-- Resources will be dynamically added here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="text-start">
                                <button type="submit" class="btn btn-primary">Save Lesson</button>
                                <a href="{{ route('admin.courses.sections.index', $section->course_id) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
