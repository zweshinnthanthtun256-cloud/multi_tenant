@extends('layouts.app')


@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold">
                Activity Logs
            </h3>

            <small class="text-muted">
                System activities history
            </small>

        </div>


    </div>




    <div class="card-box">


        <div class="table-responsive">

            <table class="table table-hover align-middle">


                <thead>

                    <tr class="text-muted">

                        <th>#</th>
                        <th>User</th>
                        <th>Company</th>
                        <th>Action</th>
                        <th>Description</th>

                        <th>Date</th>

                    </tr>

                </thead>


                <tbody>


                    @forelse($logs as $log)
                        <tr>


                            <td>
                                {{ $logs->firstItem() + $loop->index }}
                            </td>


                            <td class="fw-semibold">

                                {{ $log->user->name ?? '-' }}

                            </td>



                            <td>

                                {{ $log->company->name ?? '-' }}

                            </td>



                            <td>

                                <span class="badge bg-primary">

                                    {{ $log->action }}

                                </span>

                            </td>



                            <td>

                                {{ $log->description }}

                            </td>



                            <td>

                                {{ $log->created_at->format('Y-m-d H:i') }}

                            </td>


                        </tr>



                    @empty


                        <tr>

                            <td colspan="7" class="text-center text-muted py-4">

                                No activity found

                            </td>

                        </tr>
                    @endforelse


                </tbody>


            </table>

        </div>



        <div class="mt-3">

            {{ $logs->links() }}

        </div>


    </div>
@endsection
