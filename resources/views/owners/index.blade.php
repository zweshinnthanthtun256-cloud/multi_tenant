@extends('layouts.app')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold">Company Owners</h3>
            <small class="text-muted">Manage all company owners (Tenant DB)</small>
        </div>

        <a href="{{ route('owners.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-lg me-1"></i> Add Owner
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success rounded-3">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger rounded-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="card-box">

        <div class="table-responsive">
            <table class="table align-middle table-hover">

                <thead>
                    <tr class="text-muted">
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($owners as $owner)
                        <tr>

                            <td>{{ $owners->firstItem() + $loop->index }}</td>
                            <td>
                                {{ $owner->company->name ?? '-' }}
                            </td>

                            <td class="fw-semibold">
                                {{ $owner->name }}
                            </td>

                            <td>
                                {{ $owner->email ?? '-' }}
                            </td>

                            <td>
                                {{ $owner->phone ?? '-' }}
                            </td>

                            <td>
                                {{ $owner->address ?? '-' }}
                            </td>

                            <td>
                                <a href="{{ route('owners.show', $owner->id) }}"
                                    class="btn btn-sm btn-outline-info rounded-pill">
                                    View
                                </a>
                                <a href="{{ route('owners.edit', $owner->id) }}"
                                    class="btn btn-sm btn-outline-warning rounded-pill">
                                    Edit
                                </a>

                                <form action="{{ route('owners.destroy', $owner->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-outline-danger rounded-pill">
                                        Delete
                                    </button>
                                </form>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No company owners found
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
