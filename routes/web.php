<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\PortalAuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClientDashboardController;
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
    Route::view('/faq', 'pages.faq')->name('pages.faq');
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
        Route::redirect('/login/jobseeker', '/login')->name('login.jobseeker');
        Route::redirect('/login/job-seeker', '/login')->name('login.werkzoekende');
        Route::redirect('/login/employer', '/login')->name('login.employer');

        Route::get('/sign-up', 'showRegisterChoice')->name('register.choice');
        Route::post('/sign-up', 'register')->defaults('role', User::ROLE_TENANT_OWNER)->name('register.submit');
        Route::redirect('/sign-up/jobseeker', '/sign-up')->name('register.jobseeker');
        Route::redirect('/sign-up/job-seeker', '/sign-up')->name('register.werkzoekende');
        Route::redirect('/sign-up/employer', '/sign-up')->name('register.employer');

        Route::get('/admin/login', 'showAdminLogin')->name('admin.login');
        Route::post('/admin/login', 'login')->defaults('role', User::ROLE_ADMIN)->name('admin.login.submit');

        Route::post('/logout', 'logout')->middleware('auth')->name('logout');
        Route::post('/uitloggen', 'logout')->middleware('auth');

        Route::redirect('/werkzoekende/dashboard', '/client/dashboard');
        Route::redirect('/werkgever/dashboard', '/client/dashboard');

        Route::redirect('/dashboard/werkzoekende', '/client/dashboard')->name('werkzoekende.dashboard');
        Route::redirect('/dashboard/werkgever', '/client/dashboard')->name('werkgever.dashboard');
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

    Route::redirect('/dashboard/werkgever/omgeving', '/client/dashboard/environments');
    Route::redirect('/dashboard/omgeving', '/client/dashboard/environments');
    Route::redirect('/workspace/login', '/login');
    Route::get('/workspace/{path?}', function (?string $path = null) {
        return redirect('/client/dashboard'.($path ? '/'.$path : ''));
    })->where('path', '.*');

    Route::redirect('/client/dashboard/login', '/login')->name('client.login');
    Route::middleware(['auth', 'role:tenant_owner,admin'])
        ->prefix('client/dashboard')
        ->name('client.')
        ->group(function (): void {
            Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard');
            Route::get('/environments', [ClientDashboardController::class, 'section'])->defaults('section', 'environments')->name('environments.index');
            Route::get('/environments/create', [ClientDashboardController::class, 'section'])->defaults('section', 'create-environment')->name('environments.create');
            Route::get('/jobs', [ClientDashboardController::class, 'section'])->defaults('section', 'jobs')->name('jobs.index');
            Route::get('/jobs/create', [ClientDashboardController::class, 'section'])->defaults('section', 'create-job')->name('jobs.create');
            Route::get('/domains', [ClientDashboardController::class, 'domains'])->name('domains.index');
            Route::post('/domains', [ClientDashboardController::class, 'storeDomain'])->name('domains.store');
            Route::get('/domains/create', [ClientDashboardController::class, 'domains'])->name('domains.create');
            Route::post('/domains/{domain}/verify', [ClientDashboardController::class, 'verifyDomain'])->name('domains.verify');
            Route::get('/applications', [ClientDashboardController::class, 'section'])->defaults('section', 'applications')->name('applications.index');
            Route::get('/billing', [ClientDashboardController::class, 'section'])->defaults('section', 'billing')->name('billing');
            Route::get('/marketing', [ClientDashboardController::class, 'section'])->defaults('section', 'marketing')->name('marketing.index');
            Route::get('/marketing/landingpagina', [ClientDashboardController::class, 'section'])->defaults('section', 'landingpagina')->name('marketing.landingpagina');
            Route::get('/marketing/socials', [ClientDashboardController::class, 'section'])->defaults('section', 'socials')->name('marketing.socials');
            Route::get('/jobs-settings', [ClientDashboardController::class, 'section'])->defaults('section', 'jobs-settings')->name('jobs-settings.index');
            Route::get('/jobs-settings/sector', [ClientDashboardController::class, 'section'])->defaults('section', 'sector')->name('jobs-settings.sector');
            Route::get('/jobs-settings/categorie', [ClientDashboardController::class, 'section'])->defaults('section', 'categorie')->name('jobs-settings.categorie');
            Route::get('/jobs-settings/job-type', [ClientDashboardController::class, 'jobTypes'])->name('jobs-settings.job-type');
            Route::post('/jobs-settings/job-type', [ClientDashboardController::class, 'storeJobType'])->name('jobs-settings.job-type.store');
            Route::get('/jobs-settings/organization-type', [ClientDashboardController::class, 'section'])->defaults('section', 'organization-type')->name('jobs-settings.organization-type');
            Route::get('/companies', [ClientDashboardController::class, 'companies'])->name('companies.index');
            Route::post('/companies', [ClientDashboardController::class, 'storeCompany'])->name('companies.store');
            Route::get('/companies/create', [ClientDashboardController::class, 'createCompany'])->name('companies.create');
        });

    Route::get('/dashboard/billing/success', [BillingController::class, 'success'])
        ->middleware(['auth', 'role:tenant_owner'])
        ->name('billing.success');
};

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group($centralRoutes);
}
