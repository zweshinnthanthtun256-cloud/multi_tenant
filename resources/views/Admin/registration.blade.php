@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold">Registration Requests</h3>
        <small class="text-muted">
            Manage user registration requests
        </small>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="card-box">

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>#</th>
                    <th>Role</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th width="200">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($registrations as $registration)

                <tr>

                    <td>
                        {{ $registrations->firstItem() + $loop->index }}
                    </td>

                    <td>{{ $registration->role }}</td>

                    <td>{{ $registration->username }}</td>

                    <td>{{ $registration->email }}</td>

                    <td>{{ $registration->phone ?? '-' }}</td>

                    <td>{{ $registration->company_name ?? '-' }}</td>

                    <td>
                        @if($registration->status == 'pending')
                            <span class="badge bg-warning">
                                Pending
                            </span>

                        @elseif($registration->status == 'approved')
                            <span class="badge bg-success">
                                Approved
                            </span>

                        @else
                            <span class="badge bg-danger">
                                Rejected
                            </span>
                        @endif
                    </td>

                    <td>

                        <form action="{{ route('registrations.approve', $registration->id) }}"
                              method="POST"
                              class="d-inline">
                            @csrf
                            @method('PUT')

                            <button class="btn btn-success btn-sm">
                                Approve
                            </button>
                        </form>

                        <form action="{{ route('registrations.reject', $registration->id) }}"
                              method="POST"
                              class="d-inline">
                            @csrf
                            @method('PUT')

                            <button class="btn btn-danger btn-sm">
                                Reject
                            </button>
                        </form>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="8" class="text-center">
                        No registration requests found.
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-3">
        {{ $registrations->links() }}
    </div>

</div>

@endsection