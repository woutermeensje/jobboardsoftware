<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\PortalAuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ContactController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

$centralRoutes = function (): void {
    Route::get('/', function () {
        return view('welcome');
    })->name('welcome');

    Route::view('/job-seeker', 'pages.werkzoekende')->name('pages.werkzoekende');
    Route::view('/job-alerts', 'pages.job-alerts')->name('pages.job-alerts');
    Route::view('/newsletter', 'pages.nieuwsbrief')->name('pages.nieuwsbrief');
    Route::view('/employer', 'pages.werkgever')->name('pages.werkgever');
    Route::view('/post-a-job', 'pages.vacature-plaatsen')->name('pages.vacature-plaatsen');
    Route::view('/features', 'pages.features')->name('pages.features');
    Route::view('/pricing', 'pages.tarieven')->name('pages.tarieven');
    Route::view('/customers', 'pages.customers')->name('pages.customers');
    Route::view('/alternatives', 'pages.alternatives')->name('pages.alternatives');
    Route::view('/about-us', 'pages.over-ons')->name('pages.over-ons');
    Route::view('/contact', 'pages.contact')->name('pages.contact');
    Route::post('/contact', [ContactController::class, 'submit'])->name('pages.contact.submit');

    Route::redirect('/werkzoekende', '/job-seeker');
    Route::redirect('/nieuwsbrief', '/newsletter');
    Route::redirect('/werkgever', '/employer');
    Route::redirect('/vacature-plaatsen', '/post-a-job');
    Route::redirect('/tarieven', '/pricing');
    Route::redirect('/over-ons', '/about-us');

    Route::redirect('/inloggen', '/login');
    Route::redirect('/inloggen/', '/login');
    Route::redirect('/inloggen/werkzoekende', '/login');
    Route::redirect('/inloggen/werkzoekende/', '/login');
    Route::redirect('/inloggen/werkgever', '/login');
    Route::redirect('/inloggen/werkgever/', '/login');
    Route::redirect('/aanmelden', '/sign-up');
    Route::redirect('/aanmelden/', '/sign-up');
    Route::redirect('/aanmelden/werkzoekende', '/sign-up');
    Route::redirect('/aanmelden/werkzoekende/', '/sign-up');
    Route::redirect('/aanmelden/werkgever', '/sign-up');
    Route::redirect('/aanmelden/werkgever/', '/sign-up');
    Route::redirect('/admin/inloggen', '/admin/login');
    Route::redirect('/admin/inloggen/', '/admin/login');

    Route::controller(PortalAuthController::class)->group(function () {
        Route::get('/login', 'showLoginChoice')->name('login.choice');
        Route::post('/login', 'login')->defaults('role', User::ROLE_TENANT_OWNER)->name('login.submit');
        Route::redirect('/login/job-seeker', '/login')->name('login.werkzoekende');
        Route::redirect('/login/employer', '/login')->name('login.werkgever');

        Route::get('/sign-up', 'showRegisterChoice')->name('register.choice');
        Route::post('/sign-up', 'register')->defaults('role', User::ROLE_TENANT_OWNER)->name('register.submit');
        Route::redirect('/sign-up/job-seeker', '/sign-up')->name('register.werkzoekende');
        Route::redirect('/sign-up/employer', '/sign-up')->name('register.werkgever');

        Route::get('/admin/login', 'showAdminLogin')->name('admin.login');
        Route::post('/admin/login', 'login')->defaults('role', User::ROLE_ADMIN)->name('admin.login.submit');

        Route::post('/logout', 'logout')->middleware('auth')->name('logout');
        Route::post('/uitloggen', 'logout')->middleware('auth');

        Route::redirect('/werkzoekende/dashboard', '/workspace');
        Route::redirect('/werkgever/dashboard', '/workspace');

        Route::redirect('/dashboard/werkzoekende', '/workspace')->name('werkzoekende.dashboard');
        Route::redirect('/dashboard/werkgever', '/workspace')->name('werkgever.dashboard');
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->middleware(['auth', 'role:admin'])->name('admin.dashboard');
        Route::middleware(['auth', 'role:admin'])->prefix('/admin')->name('admin.')->group(function () {
            Route::get('/users', [AdminDashboardController::class, 'users'])->name('users.index');
            Route::patch('/users/{managedUser}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
            Route::get('/tenants', [AdminDashboardController::class, 'tenants'])->name('tenants.index');
            Route::patch('/tenants/{tenant}', [AdminDashboardController::class, 'updateTenant'])->name('tenants.update');
            Route::get('/domains', [AdminDashboardController::class, 'domains'])->name('domains.index');
            Route::patch('/domains/{domain}', [AdminDashboardController::class, 'updateDomain'])->name('domains.update');
            Route::get('/jobs', [AdminDashboardController::class, 'jobs'])->name('jobs.index');
            Route::patch('/jobs/{job}', [AdminDashboardController::class, 'updateJob'])->name('jobs.update');
            Route::get('/applications', [AdminDashboardController::class, 'applications'])->name('applications.index');
            Route::patch('/applications/{application}', [AdminDashboardController::class, 'updateApplication'])->name('applications.update');
        });
    });

    Route::redirect('/dashboard/werkgever/omgeving', '/workspace/environments');
    Route::redirect('/dashboard/omgeving', '/workspace/environments');

    Route::get('/dashboard/billing/success', [BillingController::class, 'success'])
        ->middleware(['auth', 'role:tenant_owner'])
        ->name('billing.success');
};

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group($centralRoutes);
}
