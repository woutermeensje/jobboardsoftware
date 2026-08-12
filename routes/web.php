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

    Route::view('/job-seeker', 'pages.werkzoekende')->name('pages.werkzoekende');
    Route::view('/job-alerts', 'pages.job-alerts')->name('pages.job-alerts');
    Route::view('/newsletter', 'pages.nieuwsbrief')->name('pages.nieuwsbrief');
    Route::view('/employer', 'pages.werkgever')->name('pages.werkgever');
    Route::view('/post-a-job', 'pages.vacature-plaatsen')->name('pages.vacature-plaatsen');
    Route::view('/pricing', 'pages.tarieven')->name('pages.tarieven');
    Route::view('/about-us', 'pages.over-ons')->name('pages.over-ons');
    Route::view('/contact', 'pages.contact')->name('pages.contact');

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

        Route::redirect('/werkzoekende/dashboard', '/dashboard');
        Route::redirect('/werkgever/dashboard', '/dashboard');

        Route::get('/dashboard', 'tenantOwnerDashboard')->middleware(['auth', 'role:tenant_owner'])->name('tenant.owner.dashboard');
        Route::redirect('/dashboard/werkzoekende', '/dashboard')->name('werkzoekende.dashboard');
        Route::redirect('/dashboard/werkgever', '/dashboard')->name('werkgever.dashboard');
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

    Route::redirect('/dashboard/werkgever/omgeving', '/dashboard/environments');
    Route::redirect('/dashboard/omgeving', '/dashboard/environments');
    Route::get('/dashboard/omgeving/{tenant}/vacatures', fn (string $tenant) => redirect("/dashboard/environments/{$tenant}/jobs"));
    Route::get('/dashboard/omgeving/{tenant}/vacatures/nieuw', fn (string $tenant) => redirect("/dashboard/environments/{$tenant}/jobs/new"));
    Route::get('/dashboard/omgeving/{tenant}/vacatures/{job}/bewerken', fn (string $tenant, string $job) => redirect("/dashboard/environments/{$tenant}/jobs/{$job}/edit"));
    Route::get('/dashboard/omgeving/{tenant}/sollicitaties', fn (string $tenant) => redirect("/dashboard/environments/{$tenant}/applications"));

    Route::middleware(['auth', 'role:tenant_owner'])->group(function () {
        Route::get('/dashboard/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');

        Route::get('/dashboard/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/dashboard/billing/plan', [BillingController::class, 'selectPlan'])->name('billing.plan.select');
        Route::get('/dashboard/billing/success', [BillingController::class, 'success'])->name('billing.success');

        Route::get('/dashboard/environments', [TenantEnvironmentController::class, 'index'])->name('tenant.environments.index');
        Route::post('/dashboard/environments', [TenantEnvironmentController::class, 'store'])->name('tenant.environments.store');
        Route::post('/dashboard/environments/{tenant}/domains', [TenantEnvironmentController::class, 'storeDomain'])->name('tenant.environments.domains.store');
        Route::post('/dashboard/environments/{tenant}/domains/{domain}/check', [TenantEnvironmentController::class, 'checkDomain'])->name('tenant.environments.domains.check');
        Route::post('/dashboard/environments/{tenant}/domains/{domain}/ssl', [TenantEnvironmentController::class, 'issueSsl'])->name('tenant.environments.domains.ssl');

        Route::post('/dashboard/omgeving', [TenantEnvironmentController::class, 'store']);
        Route::post('/dashboard/omgeving/{tenant}/domeinen', [TenantEnvironmentController::class, 'storeDomain']);
        Route::post('/dashboard/omgeving/{tenant}/domeinen/{domain}/controleer', [TenantEnvironmentController::class, 'checkDomain']);
        Route::post('/dashboard/omgeving/{tenant}/domeinen/{domain}/ssl', [TenantEnvironmentController::class, 'issueSsl']);

        Route::get('/dashboard/environments/{tenant}/jobs', [TenantJobController::class, 'index'])->name('tenant.jobs.index');
        Route::get('/dashboard/environments/{tenant}/jobs/new', [TenantJobController::class, 'create'])->name('tenant.jobs.create');
        Route::post('/dashboard/environments/{tenant}/jobs', [TenantJobController::class, 'store'])->name('tenant.jobs.store');
        Route::get('/dashboard/environments/{tenant}/jobs/{job}/edit', [TenantJobController::class, 'edit'])->name('tenant.jobs.edit');
        Route::put('/dashboard/environments/{tenant}/jobs/{job}', [TenantJobController::class, 'update'])->name('tenant.jobs.update');
        Route::delete('/dashboard/environments/{tenant}/jobs/{job}', [TenantJobController::class, 'destroy'])->name('tenant.jobs.destroy');

        Route::post('/dashboard/omgeving/{tenant}/vacatures', [TenantJobController::class, 'store']);
        Route::put('/dashboard/omgeving/{tenant}/vacatures/{job}', [TenantJobController::class, 'update']);
        Route::delete('/dashboard/omgeving/{tenant}/vacatures/{job}', [TenantJobController::class, 'destroy']);

        Route::get('/dashboard/environments/{tenant}/applications', [TenantApplicationController::class, 'index'])->name('tenant.applications.index');
        Route::patch('/dashboard/environments/{tenant}/applications/{application}', [TenantApplicationController::class, 'update'])->name('tenant.applications.update');
        Route::patch('/dashboard/omgeving/{tenant}/sollicitaties/{application}', [TenantApplicationController::class, 'update']);
    });
};

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group($centralRoutes);
}
