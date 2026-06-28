@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold">Edit Company</h3>
        <small class="text-muted">Update company information</small>
    </div>

    <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
        <i class="bi bi-arrow-left me-1"></i> Back
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

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">

        <form action="{{ route('companies.update', $company->id) }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="name"
                           value="{{ old('name', $company->name) }}"
                           class="form-control rounded-3" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email"
                           value="{{ old('email', $company->email) }}"
                           class="form-control rounded-3">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone"
                           value="{{ old('phone', $company->phone) }}"
                           class="form-control rounded-3">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <input type="text" name="website"
                           value="{{ old('website', $company->website) }}"
                           class="form-control rounded-3">
                </div>

                <div class="col-md-6">
    <label class="form-label">Company Logo</label><br>

    @if($company->logo)

        {{-- Current / Preview Logo --}}
        <img id="logoPreview"
             src="{{ asset('uploads/company/'.$company->logo) }}"
             alt="Company Logo"
             
             width="100"
             height="100"
             style="object-fit:cover; cursor:pointer;">

    @else

        {{-- Empty Preview --}}
        <img id="logoPreview"
             alt="Logo Preview"
             class="rounded-circle border shadow-sm d-none"
             width="100"
             height="100"
             style="object-fit:cover; cursor:pointer;">

    @endif

    <input type="file"
           name="logo"
           id="logoInput"
           class="form-control rounded-3 mt-3"
           accept="image/*">
</div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select rounded-3">
                        <option value="1" {{ $company->status ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$company->status ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control rounded-3" rows="2">
                        {{ old('address', $company->address) }}
                    </textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control rounded-3" rows="3">
                        {{ old('description', $company->description) }}
                    </textarea>
                </div>

                <div class="col-12 text-end">
                    <button class="btn btn-warning rounded-pill px-5">
                        Update Company
                    </button>
                </div>

            </div>

        </form>

    </div>
</div>
{{-- Fullscreen Logo Modal --}}
<div class="modal fade" id="logoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-dark border-0">

            <div class="text-end p-3">
                <button type="button"
                        class="btn btn-light rounded-circle"
                        data-bs-dismiss="modal">
                    ✕
                </button>
            </div>

            <div class="d-flex justify-content-center align-items-center h-100">
                <img id="fullLogoPreview"
                     class="img-fluid rounded-4 shadow-lg"
                     style="max-height:90vh; object-fit:contain;">
            </div>

        </div>
    </div>
</div>
<script>
const logoInput = document.querySelector('input[name="logo"]');
const logoPreview = document.getElementById('logoPreview');
const fullLogoPreview = document.getElementById('fullLogoPreview');

logoInput?.addEventListener('change', function (e) {

    const file = e.target.files[0];

    if (file) {

        const reader = new FileReader();

        reader.onload = function (e) {

            logoPreview.src = e.target.result;
            fullLogoPreview.src = e.target.result;

            logoPreview.style.display = 'block';
        };

        reader.readAsDataURL(file);
    }
});

logoPreview?.addEventListener('click', function () {

    const modal = new bootstrap.Modal(
        document.getElementById('logoModal')
    );

    modal.show();
});
</script>

@endsection