@extends('layouts.app')

@section('content')
<div class="container">

    <h3>Add Employee</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('company_admin.employees.store') }}" method="POST">
        @csrf

        <div class="mb-2">
            <label>Name</label>
            <input type="text" name="name" class="form-control">
        </div>

        <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="mb-2">
            <label>Password</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-2">
            <label>Role</label>
            <select name="role" class="form-control">
                <option value="Manager">Manager</option>
                <option value="Staff">Staff</option>
            </select>
        </div>

        <div class="mb-2">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>

        <div class="mb-2">
            <label>Position</label>
            <input type="text" name="position" class="form-control">
        </div>

        <button class="btn btn-primary">Save</button>
    </form>

</div>
@endsection