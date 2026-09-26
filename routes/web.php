<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyAdmin\EmployeeInvitationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyOwnerController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\WorkspaceSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ClientController::class, 'index'])->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/register', [ClientController::class, 'register'])->name('register');
    Route::post('/register', [ClientController::class, 'registerSubmit'])->middleware('throttle:public-forms')->name('register.submit');
    Route::view('/login', 'Admin.login')->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.submit');
    Route::view('/forgot-password', 'auth.forgot')->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:public-forms')->name('password.email');
    Route::get('/reset-password/{token}', fn (string $token) => view('auth.reset', ['token' => $token]))->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:public-forms')->name('password.update');
    Route::get('/invitations/{token}', [EmployeeInvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{token}', [EmployeeInvitationController::class, 'complete'])->middleware('throttle:public-forms')->name('invitations.complete');
});
Route::post('/admin/logout', [AdminController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'active'])->group(function () {
    Route::view('/email/verify', 'auth.verify')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->middleware('verified')->name('dashboard');
});
Route::prefix('admin')->name('admin.')->middleware(['auth', 'active', 'verified', 'role:Super Admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/registrations', [AdminController::class, 'registerList'])->name('registrations.index');
    Route::put('/registrations/{id}/approve', [AdminController::class, 'approve'])->name('registrations.approve');
    Route::put('/registrations/{id}/reject', [AdminController::class, 'reject'])->name('registrations.reject');
    Route::resource('employees', EmployeeController::class)->except('show');
    Route::patch('employees/{employee}/status', [EmployeeController::class, 'changeStatus'])->name('employees.status');
    Route::resource('companies', CompanyController::class);
    Route::post('companies/{id}/restore', [CompanyController::class, 'restore'])->name('companies.restore');
    Route::resource('owners', CompanyOwnerController::class);
    Route::resource('roles', RoleController::class)->except('show');
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');
    Route::get('billing', [BillingController::class, 'admin'])->name('billing');
    Route::post('billing', [BillingController::class, 'store'])->name('billing.store');
    Route::post('billing/{invoice}/paid', [BillingController::class, 'paid'])->name('billing.paid');
    Route::post('billing/{invoice}/reject', [BillingController::class, 'rejectPayment'])->name('billing.reject');
});
Route::middleware(['auth', 'active', 'verified', 'role:Company Admin'])->prefix('company-admin')->name('company_admin.')->group(function () {
    Route::redirect('/dashboard', '/crm')->name('dashboard');
    Route::resource('employees', EmployeeController::class)->except('show');
    Route::patch('employees/{employee}/status', [EmployeeController::class, 'changeStatus'])->name('employees.status');
    Route::get('invitations/create', [EmployeeInvitationController::class, 'create'])->name('invitations.create');
    Route::post('invitations', [EmployeeInvitationController::class, 'store'])->middleware('throttle:10,1')->name('invitations.store');
    Route::delete('invitations/{invitation}', [EmployeeInvitationController::class, 'revoke'])->name('invitations.revoke');
});
Route::middleware(['auth', 'active', 'verified', 'role:Company Admin|Manager|Staff'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/billing/{invoice}/submit-payment', [BillingController::class, 'submitPayment'])->name('billing.submit-payment');
    Route::get('/workspace/settings', [WorkspaceSettingsController::class, 'index'])->name('workspace.settings');
    Route::put('/workspace/settings', [WorkspaceSettingsController::class, 'update'])->name('workspace.update');
    Route::get('/workspace/export', [WorkspaceSettingsController::class, 'export'])->name('workspace.export');
    Route::prefix('crm')->name('crm.')->middleware('subscription')->group(function () {
        Route::get('/', [CrmController::class, 'dashboard'])->name('dashboard');
        Route::get('contacts', [CrmController::class, 'contacts'])->name('contacts');
        Route::get('contacts/export', [CrmController::class, 'export'])->name('contacts.export');
        Route::post('contacts/import', [CrmController::class, 'import'])->name('contacts.import');
        Route::get('contacts/create', [CrmController::class, 'contactForm'])->name('contacts.create');
        Route::post('contacts', [CrmController::class, 'saveContact'])->name('contacts.store');
        Route::get('contacts/{contact}/edit', [CrmController::class, 'contactForm'])->name('contacts.edit');
        Route::put('contacts/{contact}', [CrmController::class, 'saveContact'])->name('contacts.update');
        Route::delete('contacts/{contact}', [CrmController::class, 'deleteContact'])->name('contacts.destroy');
        Route::get('deals', [CrmController::class, 'deals'])->name('deals');
        Route::get('deals/create', [CrmController::class, 'dealForm'])->name('deals.create');
        Route::post('deals', [CrmController::class, 'saveDeal'])->name('deals.store');
        Route::get('deals/{deal}/edit', [CrmController::class, 'dealForm'])->name('deals.edit');
        Route::put('deals/{deal}', [CrmController::class, 'saveDeal'])->name('deals.update');
        Route::delete('deals/{deal}', [CrmController::class, 'deleteDeal'])->name('deals.destroy');
        Route::get('tasks', [CrmController::class, 'tasks'])->name('tasks');
        Route::post('tasks', [CrmController::class, 'storeTask'])->name('tasks.store');
        Route::patch('tasks/{task}', [CrmController::class, 'updateTask'])->name('tasks.update');
        Route::get('assistant', [AiController::class, 'index'])->name('ai');
        Route::post('assistant', [AiController::class, 'generate'])->middleware('throttle:ai')->name('ai.generate');
        Route::put('assistant/consent', [AiController::class, 'consent'])->name('ai.consent');
    });
});
