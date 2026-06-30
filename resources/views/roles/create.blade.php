@extends('layouts.app')

@section('content')

<div class="card-box">

    <h4>Create Role</h4>

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf

        <div class="mb-3">
            <label>Role Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <button class="btn btn-primary">
            Save
        </button>

        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
            Back
        </a>
    </form>

</div>

@endsection