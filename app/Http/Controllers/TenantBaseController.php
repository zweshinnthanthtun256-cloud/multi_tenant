<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware; 
use App\Http\Middleware\TenantIdentification;

class TenantBaseController extends Controller implements HasMiddleware
{
    /**
     * Laravel 11 အတွက် Middleware သတ်မှတ်သည့် ပုံစံအသစ်
     */
    public static function middleware(): array
    {
        return [
            'auth', // ပုံမှန် Auth စစ်မည်
            TenantIdentification::class, // မိမိတို့ဆောက်ထားသော Tenant ပြောင်းပေးမည့် Middleware ခေါ်မည်
        ];
    }
}