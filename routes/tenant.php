<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantFrontendController;
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
    Route::get('/vacatures', [TenantFrontendController::class, 'jobs'])->name('tenant.jobs');
    Route::get('/vacatures/{job:slug}', [TenantFrontendController::class, 'showJob'])->name('tenant.jobs.show');
    Route::post('/vacatures/{job:slug}/solliciteren', [TenantFrontendController::class, 'apply'])->name('tenant.jobs.apply');
    Route::get('/contact', [TenantFrontendController::class, 'contact'])->name('tenant.contact');
});
