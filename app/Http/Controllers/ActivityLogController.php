<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {

        $logs = ActivityLog::with(
            'user',
            'company'
        )
            ->latest()
            ->paginate(15);

        return view(
            'activity_logs.index',
            compact('logs')
        );

    }
}
