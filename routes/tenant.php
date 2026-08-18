<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantFrontendController;
use App\Http\Controllers\Auth\PortalAuthController;
use App\Http\Controllers\TenantDashboardController;
use App\Models\TenantJob;
use App\Models\User;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', [TenantFrontendController::class, 'home'])->name('tenant.home');
    Route::get('/jobs', [TenantFrontendController::class, 'jobs'])->name('tenant.jobs');
    Route::get('/jobs/{job:slug}', [TenantFrontendController::class, 'showJob'])->name('tenant.jobs.show');
    Route::post('/jobs/{job:slug}/apply', [TenantFrontendController::class, 'apply'])->name('tenant.jobs.apply');
    Route::get('/post-a-job', [TenantFrontendController::class, 'showPostJob'])->name('tenant.post-job');
    Route::post('/post-a-job', [TenantFrontendController::class, 'storePostJob'])->name('tenant.post-job.store');
    Route::get('/pricing', [TenantFrontendController::class, 'pricing'])->name('tenant.pricing');
    Route::get('/contact', [TenantFrontendController::class, 'contact'])->name('tenant.contact');
    Route::get('/job-alerts', [TenantFrontendController::class, 'jobAlerts'])->name('tenant.job-alerts');
    Route::post('/job-alerts', [TenantFrontendController::class, 'storeJobAlert'])->name('tenant.job-alerts.store');
    Route::get('/newsletter', [TenantFrontendController::class, 'newsletter'])->name('tenant.newsletter');
    Route::post('/newsletter', [TenantFrontendController::class, 'storeNewsletter'])->name('tenant.newsletter.store');

    Route::controller(PortalAuthController::class)->group(function (): void {
        Route::get('/login', 'showTenantLoginChoice')->name('tenant.login.choice');
        Route::get('/login/jobseeker', 'showTenantJobseekerLogin')->name('tenant.login.jobseeker');
        Route::get('/login/job-seeker', fn () => redirect()->route('tenant.login.jobseeker'));
        Route::post('/login/jobseeker', 'tenantLogin')
            ->defaults('role', User::ROLE_JOBSEEKER)
            ->name('tenant.login.jobseeker.submit');
        Route::get('/login/employer', 'showTenantEmployerLogin')->name('tenant.login.employer');
        Route::post('/login/employer', 'tenantLogin')
            ->defaults('role', User::ROLE_EMPLOYER)
            ->name('tenant.login.employer.submit');

        Route::get('/sign-up', 'showTenantRegisterChoice')->name('tenant.register.choice');
        Route::get('/sign-up/jobseeker', 'showTenantJobseekerRegister')->name('tenant.register.jobseeker');
        Route::get('/sign-up/job-seeker', fn () => redirect()->route('tenant.register.jobseeker'));
        Route::post('/sign-up/jobseeker', 'tenantRegister')
            ->defaults('role', User::ROLE_JOBSEEKER)
            ->name('tenant.register.jobseeker.submit');
        Route::get('/sign-up/employer', 'showTenantEmployerRegister')->name('tenant.register.employer');
        Route::post('/sign-up/employer', 'tenantRegister')
            ->defaults('role', User::ROLE_EMPLOYER)
            ->name('tenant.register.employer.submit');

        Route::post('/logout', 'tenantLogout')->name('tenant.logout');
    });

    Route::get('/account', [TenantDashboardController::class, 'account'])->name('tenant.account');
    Route::get('/jobseeker/dashboard', [TenantDashboardController::class, 'jobseeker'])->name('tenant.jobseeker.dashboard');
    Route::get('/employer/dashboard', [TenantDashboardController::class, 'employer'])->name('tenant.employer.dashboard');

    Route::redirect('/vacatures', '/jobs');
    Route::redirect('/tarieven', '/pricing');
    Route::get('/vacatures/{job:slug}', fn (TenantJob $job) => redirect()->route('tenant.jobs.show', $job));
    Route::post('/vacatures/{job:slug}/solliciteren', [TenantFrontendController::class, 'apply']);
});
