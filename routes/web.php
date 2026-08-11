<?php

use App\Http\Controllers\Auth\PortalAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\TenantApplicationController;
use App\Http\Controllers\TenantEnvironmentController;
use App\Http\Controllers\TenantJobController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

$centralRoutes = function (): void {
    Route::get('/', function () {
        return view('welcome');
    })->name('welcome');

    Route::view('/werkzoekende', 'pages.werkzoekende')->name('pages.werkzoekende');
    Route::view('/job-alerts', 'pages.job-alerts')->name('pages.job-alerts');
    Route::view('/nieuwsbrief', 'pages.nieuwsbrief')->name('pages.nieuwsbrief');
    Route::view('/werkgever', 'pages.werkgever')->name('pages.werkgever');
    Route::view('/vacature-plaatsen', 'pages.vacature-plaatsen')->name('pages.vacature-plaatsen');
    Route::view('/tarieven', 'pages.tarieven')->name('pages.tarieven');
    Route::view('/over-ons', 'pages.over-ons')->name('pages.over-ons');
    Route::view('/contact', 'pages.contact')->name('pages.contact');

    Route::redirect('/inloggen/', '/inloggen');
    Route::redirect('/inloggen/werkzoekende/', '/inloggen');
    Route::redirect('/inloggen/werkgever/', '/inloggen');
    Route::redirect('/aanmelden/', '/aanmelden');
    Route::redirect('/aanmelden/werkzoekende/', '/aanmelden');
    Route::redirect('/aanmelden/werkgever/', '/aanmelden');
    Route::redirect('/admin/inloggen/', '/admin/inloggen');

    Route::controller(PortalAuthController::class)->group(function () {
        Route::get('/inloggen', 'showLoginChoice')->name('login.choice');
        Route::post('/inloggen', 'login')->defaults('role', User::ROLE_TENANT_OWNER)->name('login.submit');
        Route::redirect('/inloggen/werkzoekende', '/inloggen')->name('login.werkzoekende');
        Route::redirect('/inloggen/werkgever', '/inloggen')->name('login.werkgever');

        Route::get('/aanmelden', 'showRegisterChoice')->name('register.choice');
        Route::post('/aanmelden', 'register')->defaults('role', User::ROLE_TENANT_OWNER)->name('register.submit');
        Route::redirect('/aanmelden/werkzoekende', '/aanmelden')->name('register.werkzoekende');
        Route::redirect('/aanmelden/werkgever', '/aanmelden')->name('register.werkgever');

        Route::get('/admin/inloggen', 'showAdminLogin')->name('admin.login');
        Route::post('/admin/inloggen', 'login')->defaults('role', User::ROLE_ADMIN)->name('admin.login.submit');

        Route::post('/uitloggen', 'logout')->middleware('auth')->name('logout');

        Route::redirect('/werkzoekende/dashboard', '/dashboard');
        Route::redirect('/werkgever/dashboard', '/dashboard');

        Route::get('/dashboard', 'tenantOwnerDashboard')->middleware(['auth', 'role:tenant_owner'])->name('tenant.owner.dashboard');
        Route::redirect('/dashboard/werkzoekende', '/dashboard')->name('werkzoekende.dashboard');
        Route::redirect('/dashboard/werkgever', '/dashboard')->name('werkgever.dashboard');
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->middleware(['auth', 'role:admin'])->name('admin.dashboard');
    });

    Route::redirect('/dashboard/werkgever/omgeving', '/dashboard/omgeving');

    Route::middleware(['auth', 'role:tenant_owner'])->group(function () {
        Route::get('/dashboard/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');

        Route::get('/dashboard/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/dashboard/billing/plan', [BillingController::class, 'selectPlan'])->name('billing.plan.select');
        Route::get('/dashboard/billing/success', [BillingController::class, 'success'])->name('billing.success');

        Route::get('/dashboard/omgeving', [TenantEnvironmentController::class, 'index'])->name('tenant.environments.index');
        Route::post('/dashboard/omgeving', [TenantEnvironmentController::class, 'store'])->name('tenant.environments.store');
        Route::post('/dashboard/omgeving/{tenant}/domeinen', [TenantEnvironmentController::class, 'storeDomain'])->name('tenant.environments.domains.store');
        Route::post('/dashboard/omgeving/{tenant}/domeinen/{domain}/controleer', [TenantEnvironmentController::class, 'checkDomain'])->name('tenant.environments.domains.check');
        Route::post('/dashboard/omgeving/{tenant}/domeinen/{domain}/ssl', [TenantEnvironmentController::class, 'issueSsl'])->name('tenant.environments.domains.ssl');

        Route::get('/dashboard/omgeving/{tenant}/vacatures', [TenantJobController::class, 'index'])->name('tenant.jobs.index');
        Route::get('/dashboard/omgeving/{tenant}/vacatures/nieuw', [TenantJobController::class, 'create'])->name('tenant.jobs.create');
        Route::post('/dashboard/omgeving/{tenant}/vacatures', [TenantJobController::class, 'store'])->name('tenant.jobs.store');
        Route::get('/dashboard/omgeving/{tenant}/vacatures/{job}/bewerken', [TenantJobController::class, 'edit'])->name('tenant.jobs.edit');
        Route::put('/dashboard/omgeving/{tenant}/vacatures/{job}', [TenantJobController::class, 'update'])->name('tenant.jobs.update');
        Route::delete('/dashboard/omgeving/{tenant}/vacatures/{job}', [TenantJobController::class, 'destroy'])->name('tenant.jobs.destroy');

        Route::get('/dashboard/omgeving/{tenant}/sollicitaties', [TenantApplicationController::class, 'index'])->name('tenant.applications.index');
        Route::patch('/dashboard/omgeving/{tenant}/sollicitaties/{application}', [TenantApplicationController::class, 'update'])->name('tenant.applications.update');
    });
};

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group($centralRoutes);
}
