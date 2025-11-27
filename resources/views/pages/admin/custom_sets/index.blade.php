@extends('layouts.app')
@section('title', 'Custom Set List')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="mb-1">Custom Set Management</h4>
            <p class="mb-4">Manage custom sets, questions, and settings here.</p>

            <div class="tab-pane fade show active" id="roles-tab" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="col-md-4">
                            <h4 class="mb-0 text-primary">Custom Set List</h4>
                        </div>
                        <div class="col-md-8">
                            <div class="row justify-content-end">

                                <div class="col-md-2 d-flex gap-2" role="group">
                                    <a href="{{ route('admin.custom_sets.index') }}">
                                        <button class="btn btn-outline-secondary">
                                            <i class="bx bx-reset"></i>
                                        </button>
                                    </a>
                                    <a href="{{ route('admin.custom_sets.create') }}">
                                        <button class="btn btn-outline-primary">
                                            <i class="bx bx-plus"></i>
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table table-hover" id="customTable">
                            <thead class="table-secondary">
                                <tr>
                                    <th>SN</th>
                                    <th>Title</th>
                                    <th>Price Details</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customs as $custom)
                                    <tr data-item-id="{{ $custom->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $custom->title }}</td>

                                        <td>
                                            @if ($custom->pricingType === 'paid')
                                                <span class="badge bg-primary">Paid</span>
                                                <div class="mt-1">
                                                    <small class="text-muted">Normal:
                                                        <del>Rs. {{ number_format($custom->normal_price, 2) }}</del>
                                                    </small><br>
                                                    <small class="text-success">Sell Price:
                                                        Rs.
                                                        {{ number_format($custom->discount_price ?? $custom->normal_price, 2) }}
                                                    </small>
                                                </div>
                                            @else
                                                <span class="badge bg-success">Free</span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-start">
                                                <span class="badge bg-primary badge-sm">You (Creator)</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($custom->is_published)
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
                                                        <a class="dropdown-item publish-unpublish-custom"
                                                            href="javascript:void(0);" data-bs-toggle="modal"
                                                            data-bs-target="#publishUnpublishModal"
                                                            data-item-id="{{ $custom->id }}"
                                                            data-item-status="{{ $custom->is_published }}"
                                                            data-item-name="{{ $custom->title }}">
                                                            @if ($custom->is_published)
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
                                                            href="{{ route('admin.custom_sets.edit', $custom->id) }}">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-warning assign-custom"
                                                            href="javascript:void(0);" data-custom-id="{{ $custom->id }}"
                                                            data-custom-title="{{ $custom->title }}">
                                                            <i class="bx bx-send me-1"></i> Assign This
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a class="dropdown-item text-info"
                                                            href="{{ route('admin.custom_sets.questions.index', $custom->id) }}">
                                                            <i class="bx bx-question-mark me-1"></i> Manage Questions
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger delete-custom"
                                                            href="javascript:void(0);" data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-custom-id="{{ $custom->id }}"
                                                            data-custom-title="{{ $custom->title }}">
                                                            <i class="bx bx-trash me-1"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($customs->count() === 0)
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bx bx-inbox bx-lg mb-2"></i>
                                                <p>No Custom Set found. <a href="{{ route('admin.custom_sets.create') }}">Create
                                                        your first custom set</a></p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <div class="m-2">
                            {{ $customs->links() }}
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
                                <h5 class="modal-title" id="deleteModalLabel">Delete Custom Set</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete the custom set: <strong id="deleteItemName"></strong>? This
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

            <!-- Assign Custom Modal -->
            <div class="modal fade" id="assignCustomModal" tabindex="-1" aria-labelledby="assignCustomModalLabel"
                aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="assignCustomForm">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="assignCustomModalLabel">Assign Custom Set to Meeting</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <p>You are assigning: <strong id="assignCustomName"></strong></p>

                                <div class="mb-3">
                                    <label for="meetingIdInput" class="form-label">Enter Zoom Meeting ID</label>
                                    <input type="text" id="meetingIdInput" class="form-control"
                                        placeholder="e.g. 123456789" required>
                                </div>

                                <input type="hidden" id="assignCustomId">
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm">Assign</button>
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
                                <h5 class="modal-title" id="publishUnpublishModalLabel">Publish/Unpublish Custom Set</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>
                                    <span id="publishUnpublishMessage"></span>
                                    <strong id="publishUnpublishCustomName"></strong>?
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

                    document.querySelectorAll('.delete-custom').forEach(button => {
                        button.addEventListener('click', function() {
                            const itemId = this.getAttribute('data-custom-id');
                            const itemName = this.getAttribute('data-custom-title');

                            const deleteRoute = "{{ route('admin.custom_sets.destroy', ':id') }}".replace(':id', itemId);
                            deleteForm.action = deleteRoute;

                            document.getElementById('deleteItemName').textContent = itemName;
                        });
                    });

                    // Publish/Unpublish Modal Setup
                    const publishUnpublishModal = new bootstrap.Modal(document.getElementById('publishUnpublishModal'));
                    const publishUnpublishForm = document.getElementById('publishUnpublishForm');

                    document.querySelectorAll('.publish-unpublish-custom').forEach(button => {
                        button.addEventListener('click', function() {
                            const customId = this.getAttribute('data-item-id');
                            const customName = this.getAttribute('data-item-name');
                            const currentStatus = this.getAttribute('data-item-status');

                            const updateRoute = "{{ route('admin.custom_sets.update-publish-status', ':id') }}".replace(':id', customId);
                            publishUnpublishForm.action = updateRoute;

                            const message = currentStatus == 1 ?
                                'Are you sure you want to unpublish this custom set:' :
                                'Are you sure you want to publish this custom set:';

                            document.getElementById('publishUnpublishCustomName').textContent = customName;
                            document.getElementById('publishUnpublishMessage').textContent = message;

                            document.getElementById('publishUnpublishButton').textContent = currentStatus == 1 ? 'Unpublish' : 'Publish';

                            publishUnpublishModal.show();
                        });
                    });

                    // Assign Custom Modal Setup
                    const assignCustomModal = new bootstrap.Modal(document.getElementById('assignCustomModal'));

                    document.querySelectorAll('.assign-custom').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const customId = this.dataset.customId;
                            const customTitle = this.dataset.customTitle;

                            document.getElementById('assignCustomId').value = customId;
                            document.getElementById('assignCustomName').textContent = customTitle;

                            assignCustomModal.show();
                        });
                    });

                    // Handle Assign Form Submit
                    document.getElementById('assignCustomForm').addEventListener('submit', function(e) {
                        e.preventDefault();

                        const customId = document.getElementById('assignCustomId').value;
                        const meetingId = document.getElementById('meetingIdInput').value;

                        if (!meetingId.trim()) {
                            alert("Please enter meeting ID");
                            return;
                        }

                        fetch(`/admin/custom_sets/${customId}/assign`, {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                    "Content-Type": "application/json"
                                },
                                body: JSON.stringify({ meeting_id: meetingId })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    alert("Custom set assigned to meeting " + meetingId);
                                    assignCustomModal.hide();
                                }
                            })
                            .catch(err => console.error(err));
                    });
                });
            </script>

        </div>
    </div>
@endsection
