@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="mb-4 text-primary">Edit Group: {{ $courseGroup->title }}</h4>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.course-groups.update', [$lesson->id, $courseGroup->id]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="groupTitle" class="form-label">Group Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="groupTitle" name="title" value="{{ old('title', $courseGroup->title) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="groupDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="groupDescription" name="description" rows="3">{{ old('description', $courseGroup->description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="groupOrder" class="form-label">Order Position</label>
                        <input type="number" class="form-control" id="groupOrder" name="order" value="{{ old('order', $courseGroup->order) }}">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Group</button>
                    <a href="{{ route('admin.courses.sections.index', $lesson->section->course_id) }}" class="btn btn-secondary ms-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
