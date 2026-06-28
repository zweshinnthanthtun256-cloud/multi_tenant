@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Company Owner Dashboard</h3>
            <small class="text-muted">Overview of your business performance</small>
        </div>

        
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted">Total Products</h6>
                    <h3 class="fw-bold"></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted">Active Manager</h6>
                    <h3 class="fw-bold text-success"></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted">Total Customers</h6>
                    <h3 class="fw-bold text-warning"></h3>
                </div>
            </div>
        </div>

    </div>
    <div class="col-lg-4">

    <div class="card-box">

        <div class="chart-title">
            Net Income
        </div>

        <div id="incomeChart"></div>

    </div>

</div>

<script>

    var incomeOptions = {
        series: [{
            name: 'Income',
            data: [5, 3, 2, 7, 5, 10]
        }],
        chart: {
            type: 'bar',
            height: 280
        },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
        }
    };

    new ApexCharts(
        document.querySelector("#incomeChart"),
        incomeOptions
    ).render();

</script>

    
    

</div>
@endsection