@extends('layouts.app')

@section('content')

<div class="container py-4">
    <h2 class="mb-4">All Generated Certificates</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <p>Total certificates: {{ $certificates->count() }}</p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Learner</th>
                <th>Serial</th>
                <th>course</th>
                <th>Folder</th>
                <th>Certificate</th>
            </tr>
        </thead>

        <tbody>
                    @foreach($certificates as $cert)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $cert->user_name }}</td>
                    <td>{{ $cert->serial_no }}</td>
                    <td>{{ $cert->course }}</td>
                    <td>{{ $cert->folder }}</td>
                    <td>
                       <a href="{{ route('admin.certificates.view', $cert) }}"
                        target="_blank"
                        class="btn btn-sm btn-outline-primary">
                            View PDF
                        </a>
                                            </td>
                </tr>
            @endforeach

        </tbody>
    </table>
</div>

@endsection
