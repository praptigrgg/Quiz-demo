@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Courses Management</h2>
    <p class="text-muted">Manage courses, their sections, and detailed content here.</p>

    {{-- Search & Filter Row --}}
    <div class="card p-4 mb-4 shadow-sm">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="text" class="form-control" placeholder="Search by course name..." style="max-width:250px;">
            <button class="btn btn-success">Filter</button>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-outline-success ms-auto">
                <i class="bi bi-plus"></i> Add Course
            </a>
        </div>
    </div>

    {{-- Course List --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0 text-success">Course List</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th style="width:60px;">SN</th>
                        <th>Name</th>
                        <th>Price Details</th>
                        <th>Thumbnail</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th class="text-center" style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($courses as $index => $course)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $course->title }}</td>

                            {{-- Price / Demo Info --}}
                            <td>
                                <span class="badge bg-success">PAID</span>
                                <div class="small text-muted">Demo Video:
                                    {{ $course->demo_video ? 'Yes' : 'No' }}
                                </div>
                            </td>

                            {{-- Thumbnail --}}
                            <td>
                                @if($course->thumbnail)
                                    <img src="{{ asset('storage/' . $course->thumbnail) }}"
                                         alt="Thumbnail"
                                         style="height:60px; width:auto; border-radius:6px;">
                                @else
                                    <span class="text-muted">No Image</span>
                                @endif
                            </td>

                            {{-- Created By --}}
                            <td>
                                <span class="badge bg-dark">YOU (CREATOR)</span>
                            </td>

                            {{-- Publish Status --}}
                            <td>
                                <span class="badge {{ rand(0,1) ? 'bg-success' : 'bg-danger' }}">
                                    {{ rand(0,1) ? 'PUBLISHED' : 'UNPUBLISHED' }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="#" class="dropdown-item text-danger">
                                                <i class="bi bi-x-circle"></i> Unpublish
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.courses.edit', $course->id) }}" class="dropdown-item text-success">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.courses.sections.create', $course->id) }}" class="dropdown-item">
                                                <i class="bi bi-layers"></i> Course Sections
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.courses.show', $course->id) }}" class="dropdown-item">
                                                <i class="bi bi-layers"></i> Show
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Delete this course?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="dropdown-item text-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No courses found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer">
            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection
