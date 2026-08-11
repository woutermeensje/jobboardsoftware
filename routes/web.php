<?php

use App\Http\Controllers\Auth\PortalAuthController;
use App\Http\Controllers\TenantEnvironmentController;
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
        Route::get('/admin/dashboard', 'dashboard')->middleware(['auth', 'role:admin'])->name('admin.dashboard');
    });

    Route::redirect('/dashboard/werkgever/omgeving', '/dashboard/omgeving');

    Route::middleware(['auth', 'role:tenant_owner'])->group(function () {
        Route::get('/dashboard/omgeving', [TenantEnvironmentController::class, 'index'])->name('tenant.environments.index');
        Route::post('/dashboard/omgeving', [TenantEnvironmentController::class, 'store'])->name('tenant.environments.store');
        Route::post('/dashboard/omgeving/{tenant}/domeinen', [TenantEnvironmentController::class, 'storeDomain'])->name('tenant.environments.domains.store');
    });
};

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group($centralRoutes);
}
