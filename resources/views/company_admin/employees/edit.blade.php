@extends('layouts.app')

@section('content')
<div class="container">

    <h3>Edit Employee</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-2">
            <label>Name</label>
            <input type="text" name="name" value="{{ $employee->user->name }}" class="form-control">
        </div>

        <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" value="{{ $employee->user->email }}" class="form-control">
        </div>

        <div class="mb-2">
            <label>Role</label>
            <select name="role" class="form-control">
                @foreach($roles as $role)
                    <option value="{{ $role->name }}"
                        {{ $employee->user->getRoleNames()->first() == $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-2">
            <label>Phone</label>
            <input type="text" name="phone" value="{{ $employee->phone }}" class="form-control">
        </div>

        <div class="mb-2">
            <label>Position</label>
            <input type="text" name="position" value="{{ $employee->position }}" class="form-control">
        </div>

        <div class="mb-2">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="active" {{ $employee->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $employee->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ $employee->status == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </div>

        <button class="btn btn-success">Update</button>
    </form>

</div>
@endsection