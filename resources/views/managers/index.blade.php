@extends('layouts.app')

@section('content')

<div class="card-box">

    <div class="d-flex justify-content-between mb-3">
        <h4>Managers</h4>
        <a href="{{ route('managers.create') }}" class="btn btn-primary btn-sm">
            + Add Manager
        </a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th width="200">Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($managers as $manager)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $manager->name }}</td>
                <td>{{ $manager->email }}</td>
                <td>
                    <a href="{{ route('managers.edit', $manager->id) }}" class="btn btn-warning btn-sm">
                        Edit
                    </a>

                    <form action="{{ route('managers.destroy', $manager->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger btn-sm">
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