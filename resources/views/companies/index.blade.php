@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold">Company List</h3>
        <small class="text-muted">Manage all registered companies</small>
    </div>

    <a href="{{ route('companies.create') }}" class="btn btn-primary rounded-pill px-4">
        <i class="bi bi-plus-lg me-1"></i> Add Company
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3">
        {{ session('success') }}
    </div>
@endif

<div class="card-box">

    <div class="table-responsive">
        <table class="table align-middle table-hover">

            <thead>
                <tr class="text-muted">
                    <th>#</th>
                    <th>Logo</th>
                    <th>Company</th>
                    
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th width="180">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($companies as $company)
                    <tr>

                        <td>{{ $companies->firstItem() + $loop->index }}</td>

                        <td>
                            <img src="{{ asset('uploads/company/'.$company->logo) }}"
                                 alt="logo"
                                 
                                 width="45"
                                 height="45"
                                 style="object-fit: cover;">
                        </td>

                        <td>
                            <div class="fw-semibold">{{ $company->name }}</div>
                            <small class="text-muted">{{ $company->website }}</small>
                        </td>

                        <td>{{ $company->email ?? '-' }}</td>

                        <td>{{ $company->phone ?? '-' }}</td>

                        <td>
                            @if($company->status)
                                <span class="badge bg-success rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary rounded-pill">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('companies.show', $company->id) }}"
                               class="btn btn-sm btn-outline-primary rounded-pill">
                                View
                            </a>

                            <a href="{{ route('companies.edit', $company->id) }}"
                               class="btn btn-sm btn-outline-warning rounded-pill">
                                Edit
                            </a>

                            <form action="{{ route('companies.destroy', $company->id) }}"
                                  method="POST"
                                  class="d-inline"
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
                        <td colspan="7" class="text-center text-muted py-4">
                            No companies found
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    <div class="mt-3">
        {{ $companies->links() }}
    </div>

</div>

@endsection