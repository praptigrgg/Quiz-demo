@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Participants for Assignment #{{ $assignment->id }}</h2>
    <p class="text-muted">
        Meeting ID: <strong>{{ $assignment->meeting_id }}</strong> | Assigned At:
        <strong>{{ $assignment->assigned_at?->format('d M, Y h:i A') }}</strong>
    </p>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">SN</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Submitted At</th>
                        <th class="text-center" style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $index => $student)
                        @php
                            $submittedAt = optional(
                                $assignment->responses
                                    ->where('student_id', $student->id)
                                    ->sortBy('created_at')
                                    ->first(),
                            )->created_at;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $submittedAt ? $submittedAt->format('d M, Y h:i A') : 'Not Submitted' }}</td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end p-2">
                                        <a href="#" class="dropdown-item view-answers-btn"
                                           data-assignment="{{ $assignment->id }}"
                                           data-student="{{ $student->id }}">
                                           <i class="bi bi-eye"></i> View Answers
                                        </a>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No participants yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- View Answers Modal -->
            <div class="modal fade" id="answersModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Participant Answers</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="answersModalBody">
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border text-success mb-3"></div>
                                <p>Loading answers...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.view-answers-btn');
    if (!btn) return;

    const assignmentId = btn.dataset.assignment;
    const studentId = btn.dataset.student;

    const modalEl = document.getElementById('answersModal');
    const modal = new bootstrap.Modal(modalEl);
    const modalBody = document.getElementById('answersModalBody');

    modal.show();
    modalBody.innerHTML = `
        <div class="text-center text-muted py-5">
            <div class="spinner-border text-success mb-3"></div>
            <p>Loading answers...</p>
        </div>
    `;

    fetch(`/admin/live/assign/${assignmentId}/student/${studentId}/answers`)
        .then(res => res.json())
        .then(data => {
            let html = `<h5 class="mb-3">${data.student.name}'s Answers</h5>
                        <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Question</th>
                                <th>Answer</th>
                                <th>Correct?</th>
                                <th>Score</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>`;

            if (data.responses.length === 0) {
                html += `<tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No answers submitted yet.
                            </td>
                        </tr>`;
            } else {
                data.responses.forEach(r => {
                    html += `<tr>
                                <td>${r.question?.questionText ?? '—'}</td>
                                <td>${r.selected_option?.optionText ?? r.subjective_answer ?? '—'}</td>
                                <td>
                                    ${
                                        r.is_correct === null
                                            ? '<span class="badge bg-secondary">N/A</span>'
                                            : r.is_correct
                                                ? '<span class="badge bg-success">Correct</span>'
                                                : '<span class="badge bg-danger">Wrong</span>'
                                    }
                                </td>
                                <td>${r.score ?? 0}</td>
                                <td>${r.created_at ?? '—'}</td>
                             </tr>`;
                });
            }

            html += `</tbody></table>`;
            modalBody.innerHTML = html;
        })
        .catch(err => {
            modalBody.innerHTML = `<div class="alert alert-danger">Failed to load answers.</div>`;
            console.error(err);
        });
});
</script>
@endsection
