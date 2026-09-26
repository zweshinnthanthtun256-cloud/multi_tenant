@hasrole('Super Admin')

<div class="row g-3">

    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card-box p-3">
            <div class="stat-number fs-4">
                {{ $totalCompanies }}
            </div>
            <div class="text-muted small">
                Total Companies
            </div>
        </div>
    </div>


    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card-box p-3">
            <div class="stat-number fs-4 text-success">
                {{ $activeCompanies }}
            </div>
            <div class="text-muted small">
                Active Companies
            </div>
        </div>
    </div>


    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card-box p-3">
            <div class="stat-number fs-4 text-danger">
                {{ $inactiveCompanies }}
            </div>
            <div class="text-muted small">
                Inactive Companies
            </div>
        </div>
    </div>


    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card-box p-3">
            <div class="stat-number fs-4 text-warning">
                {{ $suspendedCompanies }}
            </div>
            <div class="text-muted small">
                Suspended Companies
            </div>
        </div>
    </div>


    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card-box p-3">
            <div class="stat-number fs-4">
                {{ $companyAdmins }}
            </div>
            <div class="text-muted small">
                Company Admins
            </div>
        </div>
    </div>


    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card-box p-3">
            <div class="stat-number fs-4">
                {{ $employees }}
            </div>
            <div class="text-muted small">
                Employees
            </div>
        </div>
    </div>


</div>


@elsehasrole('Company Admin')


<div class="row g-3">


    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card-box p-3">

            <div class="stat-number fs-4">
                {{ $totalEmployees }}
            </div>

            <div class="text-muted small">
                Total Employees
            </div>

        </div>
    </div>



    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card-box p-3">

            <div class="stat-number fs-4 text-success">
                {{ $activeEmployees }}
            </div>

            <div class="text-muted small">
                Active Employees
            </div>

        </div>
    </div>



    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card-box p-3">

            <div class="stat-number fs-4 text-danger">
                {{ $inactiveEmployees }}
            </div>

            <div class="text-muted small">
                Inactive Employees
            </div>

        </div>
    </div>



    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card-box p-3">

            <div class="stat-number fs-4">
                {{ $managers }}
            </div>

            <div class="text-muted small">
                Managers
            </div>

        </div>
    </div>



    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card-box p-3">

            <div class="stat-number fs-4">
                {{ $staffs }}
            </div>

            <div class="text-muted small">
                Staffs
            </div>

        </div>
    </div>


</div>

@endhasrole