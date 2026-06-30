@extends('layouts.app')

@section('content')

<div class="card-box">

    <h4 class="mb-3">Create Manager</h4>

    <form action="{{ route('managers.store') }}" method="POST">
        @csrf

        {{-- Company --}}
        <div class="mb-3">
            <label>Company</label>
            <select name="company_id" class="form-control" required>
                <option value="">Select Company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Name --}}
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        {{-- Phone --}}
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>

        {{-- Address --}}
        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control"></textarea>
        </div>

        <button class="btn btn-primary">
            Save
        </button>

        <a href="{{ route('managers.index') }}" class="btn btn-secondary">
            Back
        </a>

    </form>

</div>

@endsection