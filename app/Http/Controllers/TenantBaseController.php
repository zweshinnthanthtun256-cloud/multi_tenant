<?php

namespace App\Http\Controllers;

use App\Http\Middleware\TenantIdentification;
use Illuminate\Routing\Controllers\HasMiddleware;

class TenantBaseController extends Controller implements HasMiddleware
{
    /**
     * Laravel 11 အတွက် Middleware သတ်မှတ်သည့် ပုံစံအသစ်
     */
    public static function middleware(): array
    {
        return [
            'auth', TenantIdentification::class,
        ];
    }
}
