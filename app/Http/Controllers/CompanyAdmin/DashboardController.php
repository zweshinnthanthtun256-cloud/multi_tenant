<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;

        $totalEmployees = Employee::where(
            'company_id',
            $companyId
        )->count();

        $activeEmployees = Employee::where(
            'company_id',
            $companyId
        )
            ->where('status', 1)
            ->count();

        $inactiveEmployees = Employee::where(
            'company_id',
            $companyId
        )
            ->where('status', 0)
            ->count();

        $managers = User::where('company_id', $companyId)
            ->role('Manager')
            ->count();

        $staffs = User::where('company_id', $companyId)
            ->role('Staff')
            ->count();

        return view(
            'company_admin.dashboard',
            compact(
                'totalEmployees',
                'activeEmployees',
                'inactiveEmployees',
                'managers',
                'staffs'
            )
        );
    }
}
