@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Company Profile</h3>

    <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
        Back
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body text-center p-5">

        <div class="d-flex justify-content-center mb-3">
    <img src="{{ asset('uploads/company/'.$company->logo) }}"
         class="rounded-circle border shadow-sm"
         width="100"
         height="100"
         style="object-fit:cover;">
</div>

        <h4 class="fw-bold">{{ $company->name }}</h4>
        <p class="text-muted">{{ $company->website }}</p>

        <div class="row mt-4 text-start">

            <div class="col-md-6">
                <p><strong>Email:</strong> {{ $company->email ?? '-' }}</p>
                <p><strong>Phone:</strong> {{ $company->phone ?? '-' }}</p>
                <p><strong>Status:</strong>
                    @if($company->status)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </p>
            </div>

            <div class="col-md-6">
                <p><strong>Address:</strong><br> {{ $company->address ?? '-' }}</p>
                <p><strong>Description:</strong><br> {{ $company->description ?? '-' }}</p>
            </div>

        </div>

    </div>
</div>

@endsection