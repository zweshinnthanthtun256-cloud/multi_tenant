@extends('layouts.app')

@section('content')

<div class="card-box">

    <div class="d-flex justify-content-between mb-3">
        <h4>Roles</h4>
        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
            + Add Role
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Role Name</th>
                <th width="200">Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($roles as $role)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $role->name }}</td>
                    <td>
                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection