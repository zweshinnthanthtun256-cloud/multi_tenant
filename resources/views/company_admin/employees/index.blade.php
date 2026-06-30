@extends('layouts.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between mb-3">
        <h3>Employees</h3>

        <a href="{{ route('company_admin.employees.create') }}" class="btn btn-primary">
            + Add Employee
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Position</th>
                <th>Status</th>
                <th width="200">Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->email }}</td>

                    <td>
                        {{ $employee->getRoleNames()->first() }}
                    </td>

                    <td>{{ $employee->phone }}</td>
                    <td>{{ $employee->position }}</td>

                    <td>
                        @if($employee->status == 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($employee->status == 'inactive')
                            <span class="badge bg-warning">Inactive</span>
                        @else
                            <span class="badge bg-danger">Suspended</span>
                        @endif
                    </td>

                    <td class="d-flex gap-1">

                        <a href="{{ route('company_admin.employees.edit', $employee->id) }}"
                           class="btn btn-sm btn-info">
                            Edit
                        </a>

                        <form action="{{ route('company_admin.employees.destroy', $employee->id) }}"
                              method="POST">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete employee?')">
                                Delete
                            </button>
                        </form>

                        <a href="{{ route('company_admin.employees.changeStatus', $employee->id) }}"
                           class="btn btn-sm btn-secondary">
                            Status
                        </a>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No Employees Found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $employees->links() }}

</div>
@endsection