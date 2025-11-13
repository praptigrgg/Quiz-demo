@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Course</h2>

    <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>Course Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Course Thumbnail</label>
            <input type="file" name="thumbnail" class="form-control">
        </div>

        <div class="mb-3">
            <label>Course Demo Video Link</label>
            <input type="url" name="demo_video" class="form-control">
        </div>

        <div class="mb-3">
            <label>Course Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Course</button>
    </form>
</div>
@endsection
