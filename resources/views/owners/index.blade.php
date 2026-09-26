@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold">Company Owners</h3>
        <small class="text-muted">Manage all company owners</small>
    </div>

    <a href="{{ route('admin.owners.create') }}" class="btn btn-primary rounded-pill px-4">
        <i class="bi bi-plus-lg me-1"></i> Add Owner
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="card-box">

    <div class="table-responsive">
        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>#</th>
                    <th>Company</th>
                    <th>Owner Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th width="220">Action</th>
                </tr>
            </thead>

            <tbody>

            @forelse($owners as $owner)

                <tr>

                    <td>{{ $owners->firstItem() + $loop->index }}</td>

                    <td>
                        {{ $owner->company?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $owner->name }}
                    </td>

                    <td>
                        {{ $owner->email }}
                    </td>

                    <td>
                        {{ $owner->phone ?? '-' }}
                    </td>

                    <td>
                        {{ $owner->address ?? '-' }}
                    </td>

                    <td>

                        <a href="{{ route('admin.owners.show',$owner) }}"
                           class="btn btn-sm btn-info">
                            View
                        </a>

                        <a href="{{ route('admin.owners.edit',$owner) }}"
                           class="btn btn-sm btn-warning">
                            Edit
                        </a>

                        <form action="{{ route('admin.owners.destroy',$owner) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Delete this owner?')">

                            @csrf
                            @method('DELETE')

                            <button class="btn btn-sm btn-danger">
                                Delete
                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="text-center">
                        No company owners found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>
    </div>

    <div class="mt-3">
        {{ $owners->links() }}
    </div>

</div>

@endsection