@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold">Edit Company Owner</h3>
        <small class="text-muted">Update owner details</small>
    </div>

    <a href="{{ route('owners.index') }}" class="btn btn-secondary rounded-pill px-4">
        Back
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger rounded-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card-box p-4">

    <form action="{{ route('owners.update', $owner->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">

            {{-- Company --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Company *</label>

                <select name="company_id" class="form-control rounded-3" required>
                    <option value="">Select Company</option>

                    @foreach($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ $owner->company_id == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Name --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Name *</label>
                <input type="text"
                       name="name"
                       class="form-control rounded-3"
                       value="{{ old('name', $owner->name) }}"
                       required>
            </div>

            {{-- Email --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Email *</label>
                <input type="email"
                       name="email"
                       class="form-control rounded-3"
                       value="{{ old('email', $owner->email) }}"
                       required>
            </div>

            {{-- Phone --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Phone</label>
                <input type="text"
                       name="phone"
                       class="form-control rounded-3"
                       value="{{ old('phone', $owner->phone) }}">
            </div>

            {{-- Address --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Address</label>
                <input type="text"
                       name="address"
                       class="form-control rounded-3"
                       value="{{ old('address', $owner->address) }}">
            </div>

        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-warning rounded-pill px-4">
                Update Owner
            </button>

            <a href="{{ route('owners.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                Cancel
            </a>
        </div>

    </form>

</div>

@endsection