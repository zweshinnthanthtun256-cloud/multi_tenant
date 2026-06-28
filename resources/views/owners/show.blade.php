@extends('layouts.app')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold">Owner Details</h3>
            <small class="text-muted">View company owner information</small>
        </div>

        <a href="{{ route('owners.index') }}" class="btn btn-secondary rounded-pill px-4">
            Back
        </a>
    </div>

    <div class="card-box p-4">

        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="text-muted">Name</label>
                <div class="fw-semibold">{{ $owner->name }}</div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="text-muted">Email</label>
                <div class="fw-semibold">{{ $owner->email }}</div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="text-muted">Phone</label>
                <div class="fw-semibold">{{ $owner->phone }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="text-muted">Company</label>
                <div class="fw-semibold">
                    {{ $owner->company->name ?? '-' }}
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="text-muted">Address</label>
                <div class="fw-semibold">{{ $owner->address ?? '-' }}</div>
            </div>

        </div>

        <div class="mt-3 d-flex gap-2">

            <a href="{{ route('owners.edit', $owner->id) }}" class="btn btn-warning rounded-pill px-4">
                Edit
            </a>

            <form action="{{ route('owners.destroy', $owner->id) }}" method="POST"
                onsubmit="return confirm('Are you sure?')">

                @csrf
                @method('DELETE')

                <button class="btn btn-danger rounded-pill px-4">
                    Delete
                </button>
            </form>

        </div>

    </div>
@endsection
