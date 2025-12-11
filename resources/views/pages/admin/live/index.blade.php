@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-primary mb-1">Meeting Assignments</h2>
                    <p class="text-muted mb-0">Manage assigned and unassigned live sets with ease.</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.live.assign.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-reset me-1"></i>
                    </a>
                    <a href="{{ route('admin.live.assign.page') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i>
                    </a>
                </div>
            </div>
            <div id="alertContainer" class="mb-3"></div>
        </div>

        <div class="card-body">
            {{-- Tabs --}}
            <ul class="nav nav-tabs nav-fill" id="assignmentTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active py-3" data-bs-toggle="tab" data-bs-target="#all-assignments">Assigned Sets</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#unassigned">Unassigned Sets</button>
                </li>
            </ul>

            <div class="tab-content p-3">
                {{-- Assigned Table --}}
                <div class="tab-pane fade show active" id="all-assignments">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>S.N.</th>
                                    <th>Set / Question</th>
                                    <th>Task Type</th>
                                    <th>Meeting ID</th>
                                    <th>Assigned At</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sn = $assignedMeetings->firstItem() ?? 1; @endphp
                                @forelse($assignedMeetings as $assignment)
                                    @php $questions = $assignment->assignable->questions ?? []; @endphp
                                    <tr>
                                        <td>{{ $sn++ }}</td>
                                        <td>
                                            @if(count($questions) > 1)
                                                <strong>{{ class_basename($assignment->assignable_type) === 'LiveSet' ? $assignment->assignable->title ?? 'Live Set' : $assignment->assignable->quizTitle ?? 'Quiz' }}</strong>
                                                <button class="btn btn-sm btn-light ms-2 show-questions-btn" data-id="{{ $assignment->id }}">
                                                    <i class="bi bi-chevron-down small"></i> Questions
                                                </button>
                                            @elseif(count($questions) === 1)
                                                {{ $questions[0]->title ?? ($questions[0]->questionText ?? 'N/A') }}
                                            @else
                                                <em>No questions</em>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-info">{{ class_basename($assignment->assignable_type) }}</span></td>
                                        <td><span class="fw-semibold">{{ $assignment->meeting_id }}</span></td>
                                        <td>{{ $assignment->assigned_at?->format('d M, Y h:i A') }}</td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('admin.live.participants', $assignment->id) }}"><i class="bi bi-people me-2"></i>Participants</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
                                                    <li>
                                                        <form action="{{ route('admin.live.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Delete this assignment?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @if(count($questions) > 1)
                                        <tr class="question-row" id="q-{{ $assignment->id }}" style="display:none;">
                                            <td colspan="6" class="bg-light">
                                                <ol class="mb-0 ps-5">
                                                    @foreach($questions as $question)
                                                        <li>{{ $question->title ?? ($question->questionText ?? 'N/A') }}</li>
                                                    @endforeach
                                                </ol>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No assignments found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $assignedMeetings->links('pagination::bootstrap-5') }}
                </div>

                {{-- Unassigned Table --}}
                <div class="tab-pane fade" id="unassigned">
                    <div class="mb-2">
                        <button class="btn btn-primary" id="bulkAssignBtn" data-bs-toggle="modal" data-bs-target="#assignMeetingModal" data-assignable-type="live">Assign Selected</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>S.N.</th>
                                    <th>Question Text</th>
                                    <th>Set / Task Type</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sn2 = 1; @endphp
                                @forelse($unassignedLiveSets as $liveSet)
                                    @foreach($liveSet->questions ?? [] as $question)
                                        <tr>
                                            <td><input type="checkbox" class="assign-checkbox" value="{{ $liveSet->id }}"></td>
                                            <td>{{ $sn2++ }}</td>
                                            <td>{{ $question->title ?? ($question->questionText ?? 'N/A') }}</td>
                                            <td>LiveSet</td>
                                            <td>{{ $liveSet->created_at->format('d M, Y') }}</td>
                                            <td>
                                                <button class="btn btn-success btn-sm assign-btn" data-bs-toggle="modal" data-bs-target="#assignMeetingModal" data-assignable-type="live" data-assignable-id="{{ $liveSet->id }}"><i class="bi bi-link-45deg me-1"></i>Assign</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No unassigned questions</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $unassignedLiveSets->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Assign Modal --}}
    <div class="modal fade" id="assignMeetingModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="assignMeetingForm" method="POST">
                @csrf
                <input type="hidden" name="assignable_type" id="modal_assignable_type">
                <input type="hidden" name="assignable_id" id="modal_assignable_id">

                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Assign to Meeting</h5>
                        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label fw-semibold">Meeting ID</label>
                        <input type="text" class="form-control" name="meeting_id" required>
                        <div class="mb-3 mt-2">
                            <label class="form-label fw-semibold">Timer (Minutes, optional)</label>
                            <input type="number" class="form-control" name="timer" min="1" placeholder="Leave blank to use default">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Assign</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- JS --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            // Toggle questions
            document.querySelectorAll('.show-questions-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const row = document.getElementById('q-' + btn.dataset.id);
                    if (!row) return;
                    const isHidden = row.style.display === "none";
                    row.style.display = isHidden ? "table-row" : "none";
                    btn.innerHTML = isHidden ? '<i class="bi bi-chevron-up small"></i> Hide' : '<i class="bi bi-chevron-down small"></i> Questions';
                });
            });

            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.assign-checkbox');

            // Select all checkboxes
            selectAll?.addEventListener('change', () => checkboxes.forEach(cb => cb.checked = selectAll.checked));

            const assignModal = document.getElementById('assignMeetingModal');
            const bulkBtn = document.getElementById('bulkAssignBtn');

            // Open modal
            assignModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                document.getElementById('modal_assignable_type').value = button.getAttribute('data-assignable-type');

                // Collect selected IDs
                let selectedIds = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);

                // If single assign button clicked, override
                if(button.classList.contains('assign-btn') && selectedIds.length === 0){
                    selectedIds = [button.dataset.assignableId];
                }

                document.getElementById('modal_assignable_id').value = selectedIds.join(',');
            });

            // Bulk assign validation
            bulkBtn.addEventListener('click', () => {
                const selectedIds = Array.from(checkboxes).filter(cb => cb.checked);
                if(selectedIds.length === 0){
                    alert('Please select at least one item to assign.');
                    bulkBtn.setAttribute('data-bs-toggle',''); // prevent modal
                } else {
                    bulkBtn.setAttribute('data-bs-toggle','modal');
                }
            });

            // AJAX submit
            document.getElementById('assignMeetingForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const form = e.target;
                const data = new FormData(form);

                const payload = {
                    _token: data.get('_token'),
                    meeting_id: data.get('meeting_id'),
                    assignable_type: data.get('assignable_type'),
                    assignable_id: data.get('assignable_id'),
                    timer: data.get('timer')
                };

                try {
                    const res = await fetch("{{ route('admin.live.assignToMeeting') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': data.get('_token')
                        },
                        body: JSON.stringify(payload)
                    });

                    const json = await res.json();

                    if(!res.ok){
                        let errorsHtml = '';
                        if(json.errors){
                            errorsHtml = Object.values(json.errors).flat().map(err => `<div>${err}</div>`).join('');
                        } else {
                            errorsHtml = json.message || 'Something went wrong';
                        }
                        document.getElementById('alertContainer').innerHTML = `<div class="alert alert-danger alert-dismissible fade show">${errorsHtml}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                        return;
                    }

                    // Success
                    document.getElementById('alertContainer').innerHTML = `<div class="alert alert-success alert-dismissible fade show">${json.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                    form.reset();
                    bootstrap.Modal.getInstance(assignModal).hide();
                    setTimeout(() => location.reload(), 1000);

                } catch(err){
                    console.error('AJAX Error:', err);
                    document.getElementById('alertContainer').innerHTML = `<div class="alert alert-danger alert-dismissible fade show">An unexpected error occurred.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                }
            });

        });
    </script>
</div>
@endsection
