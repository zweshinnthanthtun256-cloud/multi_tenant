<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyOwnerController;
use Illuminate\Support\Facades\Route;
Route::get('/', [ClientController::class,'index'])->name('home');
Route::get('/register',[ClientController::class,'register'])->name('register');
Route::post('/register', [ClientController::class, 'registerSubmit'])
    ->name('register.submit');


    
Route::get('/login', [AdminController::class, 'index'])->name('admin.login');

Route::post('/login',[AdminController::class,'login'])->name('login.submit');
Route::get('/admin/dashboard',[AdminController::class,'dashboard'])->middleware('auth')->name('dashboard');
Route::get('/admin/registrations',[AdminController::class, 'registerList'])->name('registrations.index');
Route::put('/registrations/{id}/approve',[AdminController::class, 'approve'])->name('registrations.approve');

Route::put('/registrations/{id}/reject',[AdminController::class, 'reject'])->name('registrations.reject');
Route::post('/admin/logout',[AdminController::class,'logout'])->name('logout');

Route::resource('companies', CompanyController::class);

Route::resource('owners', CompanyOwnerController::class);

Route::get('/dashboard', [ClientController::class, 'ownerDashboard']);
// Route::middleware(['auth', 'tenant'])->group(function () {
//     Route::resource('owners', CompanyOwnerController::class);
// });



