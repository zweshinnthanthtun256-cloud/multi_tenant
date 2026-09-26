<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        $action,
        $description,
        $company_id = null
    ) {

        ActivityLog::create([

            'user_id' => Auth::id(),

            'company_id' => $company_id,

            'action' => $action,

            'description' => $description,

        ]);

    }
}
