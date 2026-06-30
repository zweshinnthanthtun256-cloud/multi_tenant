@extends('layouts.app')

@section('content')

<div class="card-box">

    <h4 class="mb-3">Edit Manager</h4>

    <form action="{{ route('managers.update', $manager->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Company --}}
        <div class="mb-3">
            <label>Company</label>
            <select name="company_id" class="form-control" required>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}"
                        {{ $manager->company_id == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Name --}}
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control"
                   value="{{ $manager->name }}" required>
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                   value="{{ $manager->email }}" required>
        </div>

        {{-- Phone --}}
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control"
                   value="{{ $manager->phone }}">
        </div>

        {{-- Address --}}
        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control">{{ $manager->address }}</textarea>
        </div>

        <button class="btn btn-primary">
            Update
        </button>

        <a href="{{ route('managers.index') }}" class="btn btn-secondary">
            Back
        </a>

    </form>

</div>

@endsection