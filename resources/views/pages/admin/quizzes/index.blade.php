@extends('layouts.app')
@section('title', 'Quiz List')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="mb-1">Quizzes Management</h4>
            <p class="mb-4">Manage quizzes, questions, and quiz settings here.</p>

            <div class="tab-pane fade show active" id="roles-tab" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="col-md-4">
                            <h4 class="mb-0 text-primary">Quiz List</h4>
                        </div>
                        <div class="col-md-8">
                            <div class="row justify-content-end">
                                <div class="col-md-6">
                                    <form action="{{ route('admin.quizzes.index') }}" method="GET">
                                        <input type="search" name="searchQuizzes" id="searchQuizzes" class="form-control"
                                            placeholder="Search by quiz name... (Press Enter to search)"
                                            value="{{ request('searchQuizzes') }}">
                                    </form>
                                </div>
                                <div class="col-md-2 d-flex gap-2" role="group">
                                    <a href="{{ route('admin.quizzes.index') }}">
                                        <button class="btn btn-outline-secondary">
                                            <i class="bx bx-reset"></i>
                                        </button>
                                    </a>
                                    <a href="{{ route('admin.quizzes.create') }}">
                                        <button class="btn btn-outline-primary">
                                            <i class="bx bx-plus"></i>
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table table-hover" id="quizTable">
                            <thead class="table-secondary">
                                <tr>
                                    <th>SN</th>
                                    <th>Quiz Title</th>

                                    <th>Price Details</th>
                                    <th>Thumbnail</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quizzes as $quiz)
                                    <tr data-item-id="{{ $quiz->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $quiz->quizTitle }}</td>

                                        <td>
                                            @if ($quiz->pricingType === 'paid')
                                                <span class="badge bg-primary">Paid</span>
                                                <div class="mt-1">
                                                    <small class="text-muted">Normal:
                                                        <del>Rs. {{ number_format($quiz->normal_price, 2) }}</del>
                                                    </small><br>
                                                    <small class="text-success">Sell Price:
                                                        Rs.
                                                        {{ number_format($quiz->discount_price ?? $quiz->normal_price, 2) }}
                                                    </small>
                                                </div>
                                            @else
                                                <span class="badge bg-success">Free</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($quiz->quizThumbnail)
                                                <img src="{{ asset('storage/' . $quiz->quizThumbnail) }}"
                                                    alt="Quiz Thumbnail"
                                                    style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                                            @else
                                                <span class="text-muted">No Image</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-start">
                                                <span class="badge bg-primary badge-sm">You (Creator)</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($quiz->is_publish)
                                                <span class="badge bg-success">Published</span>
                                            @else
                                                <span class="badge bg-danger">Unpublished</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false"
                                                    style="padding: 0.25rem 0.5rem; font-size: 1.2rem;">
                                                    &#8942; <!-- Vertical three dots -->
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item publish-unpublish-quiz"
                                                            href="javascript:void(0);" data-bs-toggle="modal"
                                                            data-bs-target="#publishUnpublishModal"
                                                            data-item-id="{{ $quiz->id }}"
                                                            data-item-status="{{ $quiz->is_publish }}"
                                                            data-item-name="{{ $quiz->quizTitle }}">
                                                            @if ($quiz->is_publish)
                                                                <span class="text-warning">
                                                                    <i class="bx bx-x-circle me-1"></i> Unpublish
                                                                </span>
                                                            @else
                                                                <span class="text-success">
                                                                    <i class="bx bx-check-circle me-1"></i> Publish
                                                                </span>
                                                            @endif
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-primary"
                                                            href="{{ route('admin.quizzes.edit', $quiz->id) }}">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-warning assign-quiz"
                                                            href="javascript:void(0);" data-quiz-id="{{ $quiz->id }}"
                                                            data-quiz-title="{{ $quiz->quizTitle }}">
                                                            <i class="bx bx-send me-1"></i> Assign Quiz
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a class="dropdown-item text-info"
                                                            href="{{ route('admin.quizzes.questions.index', $quiz->id) }}">
                                                            <i class="bx bx-question-mark me-1"></i> Manage Questions
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger delete-quiz"
                                                            href="javascript:void(0);" data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-quiz-id="{{ $quiz->id }}"
                                                            data-quiz-title="{{ $quiz->quizTitle }}">
                                                            <i class="bx bx-trash me-1"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>



                                    </tr>
                                @endforeach

                                @if ($quizzes->count() === 0)
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bx bx-inbox bx-lg mb-2"></i>
                                                <p>No quizzes found. <a href="{{ route('admin.quizzes.create') }}">Create
                                                        your first quiz</a></p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <div class="m-2">
                            {{ $quizzes->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true"
                data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="" method="POST" id="deleteForm">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Delete Quiz</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete the quiz: <strong id="deleteItemName"></strong>? This
                                    action cannot be undone.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Publish/Unpublish Modal -->
            <div class="modal fade" id="publishUnpublishModal" tabindex="-1"
                aria-labelledby="publishUnpublishModalLabel" aria-hidden="true" data-bs-backdrop="static"
                data-bs-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="publishUnpublishForm" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="modal-header">
                                <h5 class="modal-title" id="publishUnpublishModalLabel">Publish/Unpublish Quiz</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>
                                    <span id="publishUnpublishMessage"></span>
                                    <strong id="publishUnpublishQuizName"></strong>?
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="publishUnpublishButton">
                                    Publish
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Delete Modal Setup
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    const deleteForm = document.getElementById('deleteForm');

                    document.querySelectorAll('.delete-quiz').forEach(button => {
                        button.addEventListener('click', function() {
                            const itemId = this.getAttribute('data-quiz-id');
                            const itemName = this.getAttribute('data-quiz-title');

                            // Set the form action using named route
                            const deleteRoute = "{{ route('admin.quizzes.destroy', ':id') }}".replace(
                                ':id', itemId);
                            deleteForm.action = deleteRoute;

                            // Set the quiz name in the modal
                            document.getElementById('deleteItemName').textContent = itemName;
                        });
                    });

                    // Publish/Unpublish Modal Setup
                    const publishUnpublishModal = new bootstrap.Modal(document.getElementById('publishUnpublishModal'));
                    const publishUnpublishForm = document.getElementById('publishUnpublishForm');

                    document.querySelectorAll('.publish-unpublish-quiz').forEach(button => {
                        button.addEventListener('click', function() {
                            const quizId = this.getAttribute('data-item-id');
                            const quizName = this.getAttribute('data-item-name');
                            const currentStatus = this.getAttribute('data-item-status');

                            // Set the form action using named route
                            const updateRoute = "{{ route('admin.quizzes.update-publish-status', ':id') }}"
                                .replace(':id', quizId);
                            publishUnpublishForm.action = updateRoute;

                            // Update the message based on current status
                            const message = currentStatus == 1 ?
                                'Are you sure you want to unpublish this quiz:' :
                                'Are you sure you want to publish this quiz:';

                            document.getElementById('publishUnpublishQuizName').textContent = quizName;
                            document.getElementById('publishUnpublishMessage').textContent = message;

                            // Update button label
                            document.getElementById('publishUnpublishButton').textContent = currentStatus ==
                                1 ? 'Unpublish' : 'Publish';

                            publishUnpublishModal.show();
                        });
                    });
                });
                document.querySelectorAll('.assign-quiz').forEach(btn => {
                    btn.addEventListener('click', function() {

                        const quizId = this.getAttribute('data-quiz-id');
                        const quizTitle = this.getAttribute('data-quiz-title');

                        if (!confirm(`Assign quiz: ${quizTitle}?`)) return;

                        fetch(`/admin/quizzes/${quizId}/assign`, {
                                method: 'POST',
                                headers: {
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                    "Content-Type": "application/json"
                                },
                            }).then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    alert("Quiz assigned!");
                                }
                            });
                    });
                });
            </script>

        </div>
    </div>
@endsection
