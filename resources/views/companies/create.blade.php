@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold">Add Company</h3>
        <small class="text-muted">Create a new company record</small>
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

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-4">

        <form action="{{ route('companies.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">

                {{-- Company Name --}}
                <div class="col-md-6">
                    <label class="form-label">Company Name *</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           class="form-control rounded-3"
                           required>
                </div>

                {{-- Email --}}
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="form-control rounded-3"
                           required>
                </div>

                {{-- Phone --}}
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text"
                           name="phone"
                           value="{{ old('phone') }}"
                           class="form-control rounded-3">
                </div>

                {{-- Website --}}
                <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <input type="text"
                           name="website"
                           value="{{ old('website') }}"
                           class="form-control rounded-3">
                </div>

                {{-- Logo --}}
<div class="col-md-6">
    <label class="form-label">Company Logo</label>

    <img id="logoPreview"
         src="{{ isset($company->logo) ? asset('uploads/company/'.$company->logo) : '' }}"
         
         width="100"
         height="100"
         style="object-fit:cover; cursor:pointer; {{ isset($company->logo) ? '' : 'display:none;' }}">

    <input type="file"
           name="logo"
           id="logoInput"
           class="form-control rounded-3 mt-3"
           accept="image/*">
</div>
                {{-- Status --}}
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select rounded-3">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                {{-- Address --}}
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address"
                              class="form-control rounded-3"
                              rows="2">{{ old('address') }}</textarea>
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description"
                              class="form-control rounded-3"
                              rows="3">{{ old('description') }}</textarea>
                </div>

                {{-- Submit --}}
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-5">
                        <i class="bi bi-check-circle me-1"></i> Save Company
                    </button>
                </div>

            </div>

        </form>

    </div>
</div>
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
                     class="img-fluid"
                     style="max-height:90vh; object-fit:contain;">
            </div>

        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const logoInput = document.getElementById('logoInput');
    const logoPreview = document.getElementById('logoPreview');
    const fullLogoPreview = document.getElementById('fullLogoPreview');
    const modalEl = document.getElementById('logoModal');

    const modal = new bootstrap.Modal(modalEl);

    // Open modal on click
    logoPreview?.addEventListener('click', function () {
        if (!logoPreview.src) return;

        fullLogoPreview.src = logoPreview.src;
        modal.show();
    });

    // File change preview
    logoInput?.addEventListener('change', function (e) {
        const file = e.target.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (event) {
            logoPreview.src = event.target.result;
            fullLogoPreview.src = event.target.result;

            logoPreview.style.display = 'block';
        };

        reader.readAsDataURL(file);
    });

});
</script>

@endsection