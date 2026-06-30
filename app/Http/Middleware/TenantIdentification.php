<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class TenantIdentification
{
    public function handle(Request $request, Closure $next)
    {
        // ၁။ User သည် Login ဝင်ထားပြီး ကုမ္ပဏီ ID ရှိမရှိ စစ်ဆေးမည်
        if ($request->user() && $request->user()->company_id) {
            
            // ၂။ အဆိုပါ ကုမ္ပဏီရဲ့ db_name ကို ရှာဖွေမည် (Cache သုံးလျှင် ပိုမြန်ပါသည်)
            $company = Company::find($request->user()->company_id);

            if ($company && $company->db_name) {
                // ၃။ Tenant Connection ကို အလိုအလျောက် ပြောင်းလဲသတ်မှတ်မည်
                config([
                    'database.connections.tenant' => [
                        'driver' => 'mysql',
                        'host' => env('DB_HOST', '127.0.0.1'),
                        'database' => $company->db_name,
                        'username' => env('DB_USERNAME', 'root'),
                        'password' => env('DB_PASSWORD', ''),
                    ]
                ]);

                // Connection အဟောင်းကို ဖြတ်ပြီး အသစ် ပြန်ချိတ်မည်
                DB::purge('tenant');
                DB::reconnect('tenant');

                // ၄။ Default Connection ကိုပါ 'tenant' ဟု ပြောင်းလဲလိုက်ခြင်းဖြင့် 
                // နောက်ပိုင်း Model တွေ ခေါ်ရင် စဉ်းစားနေစရာမလိုဘဲ Tenant DB ထဲ တန်းရောက်သွားမည်
                DB::setDefaultConnection('tenant');
            }
        }

        return $next($request);
    }
}