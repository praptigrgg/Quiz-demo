@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Meeting Assignments</h2>
    <p class="text-muted">Manage assigned meetings and their associated tasks here.</p>

    {{-- Alert container --}}
    <div id="alertContainer" class="mt-2"></div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3" id="assignmentTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="all-assignments-tab" data-bs-toggle="tab" data-bs-target="#all-assignments"
                type="button">Assigned Sets</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="unassigned-tab" data-bs-toggle="tab" data-bs-target="#unassigned"
                type="button">Unassigned Sets</button>
        </li>
    </ul>

    <div class="tab-content" id="assignmentTabsContent">
        {{-- Assigned Table --}}
        <div class="tab-pane fade show active" id="all-assignments">
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" id="assignedTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Question Text</th>
                                <th>Task Type</th>
                                <th>Meeting ID</th>
                                <th>Assigned At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sn = $assignedMeetings->firstItem() ?? 1; @endphp
                            @forelse($assignedMeetings as $assignment)
                                @foreach ($assignment->assignable->questions ?? [] as $question)
                                    <tr>
                                        <td>{{ $sn++ }}</td>
                                        <td>{{ $question->title ?? ($question->questionText ?? 'N/A') }}</td>
                                        <td>{{ class_basename($assignment->assignable_type) }}</td>
                                        <td>{{ $assignment->meeting_id }}</td>
                                        <td>{{ $assignment->assigned_at?->format('d M, Y h:i A') }}</td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light text-dark" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.live.participants', $assignment->id) }}">
                                                            <i class="bi bi-people"></i> Participants(Students)
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#">
                                                            <i class="bi bi-pencil-square"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('admin.live.destroy', $assignment->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Delete this assignment?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No assignments found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-2">
                        {{ $assignedMeetings->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Unassigned Table --}}
        <div class="tab-pane fade" id="unassigned">
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" id="unassignedTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Question Text</th>
                                <th>Task Type</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sn2 = 1; @endphp
                            @forelse($unassignedLiveSets as $liveSet)
                                @foreach ($liveSet->questions ?? [] as $question)
                                    <tr data-assignable-id="{{ $liveSet->id }}" data-assignable-type="live">
                                        <td>{{ $sn2++ }}</td>
                                        <td>{{ $question->title ?? ($question->questionText ?? 'N/A') }}</td>
                                        <td>LiveSet</td>
                                        <td>{{ $liveSet->created_at->format('d M, Y') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-success assign-btn" data-bs-toggle="modal"
                                                data-bs-target="#assignMeetingModal"
                                                data-assignable-id="{{ $liveSet->id }}" data-assignable-type="live">
                                                Assign
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No unassigned sets</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-2">
                        {{ $unassignedLiveSets->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Modal -->
    <div class="modal fade" id="assignMeetingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="assignMeetingForm" method="POST" action="{{ route('admin.live.assignToMeeting') }}">
                @csrf
                <input type="hidden" name="assignable_type" id="modal_assignable_type">
                <input type="hidden" name="assignable_id" id="modal_assignable_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign to Zoom Meeting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label>Meeting ID</label>
                        <input type="text" name="meeting_id" id="modal_meeting_id" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Assign</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalTypeInput = document.getElementById('modal_assignable_type');
    const modalIdInput = document.getElementById('modal_assignable_id');
    const assignForm = document.getElementById('assignMeetingForm');
    const assignModal = new bootstrap.Modal(document.getElementById('assignMeetingModal'));
    const alertContainer = document.getElementById('alertContainer');

    // Event delegation for assign buttons
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.assign-btn');
        if (btn) {
            modalTypeInput.value = btn.dataset.assignableType;
            modalIdInput.value = btn.dataset.assignableId;
        }
    });

    assignForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(assignForm);
        const submitBtn = assignForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Assigning...';

        fetch(assignForm.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        })
        .then(async res => {
            if (!res.ok) {
                const text = await res.text();
                throw new Error(`HTTP ${res.status}: ${text}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                // Close modal
                assignModal.hide();

                // Remove from unassigned table
                const unassignedTable = document.querySelector('#unassignedTable tbody');
                unassignedTable.querySelectorAll('tr').forEach(row => {
                    const btn = row.querySelector('.assign-btn');
                    if (btn && btn.dataset.assignableId == formData.get('assignable_id')) {
                        row.remove();
                    }
                });

                // Add to assigned table
                const assignedTable = document.querySelector('#assignedTable tbody');
                let sn = assignedTable.children.length ?
                    parseInt(assignedTable.children[assignedTable.children.length - 1].children[0].textContent) + 1 :
                    1;

                data.questions.forEach(q => {
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td>${sn++}</td>
                        <td>${q.title ?? q.questionText ?? 'N/A'}</td>
                        <td><span class="badge bg-info">${formData.get('assignable_type')}</span></td>
                        <td class="text-success fw-semibold">${formData.get('meeting_id')}</td>
                        <td>${data.assigned_at}</td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end p-2">
                                    <form action="/admin/live/${data.assignment_id}" method="POST" class="mt-1" onsubmit="return confirm('Delete this assignment?')">
                                        <input type="hidden" name="_token" value="${formData.get('_token')}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="dropdown-item text-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </ul>
                            </div>
                        </td>
                    `;
                    assignedTable.appendChild(newRow);
                });

                // Show success alert
                alertContainer.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Meeting assigned successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                setTimeout(() => {
                    const alert = bootstrap.Alert.getOrCreateInstance(alertContainer.querySelector('.alert'));
                    alert.close();
                }, 4000);

                assignForm.reset();
            } else {
                alertContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${data.message || 'Assignment failed!'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Assign Meeting Error:', err);
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Something went wrong! Check console.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Assign';
        });
    });
});
</script>

@endsection
