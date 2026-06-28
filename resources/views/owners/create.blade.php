@extends('layouts.app')


@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold">Add Company Owner</h3>
            <small class="text-muted">Create a new owner record</small>
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

        <form action="{{ route('owners.store') }}" method="POST">
            @csrf

            <div class="row">

                {{-- Name --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}"
                        required>
                </div>

                {{-- Email --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}"
                        required>
                </div>

                {{-- Phone --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone *</label>
                    <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone') }}"
                        required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Company *</label>

                    <select name="company_id" class="form-control rounded-3" required>
                        <option value="">Select Company</option>

                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
    <label class="form-label">Password *</label>
    <input type="password"
           name="password"
           class="form-control rounded-3"
           required>
</div>

                {{-- Address --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control rounded-3" value="{{ old('address') }}">
                </div>

            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    Save Owner
                </button>

                <a href="{{ route('owners.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    Cancel
                </a>
            </div>

        </form>

    </div>

@endsection
