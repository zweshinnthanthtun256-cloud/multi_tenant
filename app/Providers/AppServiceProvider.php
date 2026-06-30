<?php

namespace App\Providers;

use App\Models\RegistrationRequest;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.topbar',function($view){
            $notificationCount = RegistrationRequest::where('status','pending')->count();
            $view->with('notificationCount',$notificationCount);
        });
    }
}
