<?php

use App\Http\Controllers\Auth\PortalAuthController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

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
Route::redirect('/inloggen/werkzoekende/', '/inloggen/werkzoekende');
Route::redirect('/inloggen/werkgever/', '/inloggen/werkgever');
Route::redirect('/aanmelden/', '/aanmelden');
Route::redirect('/aanmelden/werkzoekende/', '/aanmelden/werkzoekende');
Route::redirect('/aanmelden/werkgever/', '/aanmelden/werkgever');
Route::redirect('/admin/inloggen/', '/admin/inloggen');

Route::controller(PortalAuthController::class)->group(function () {
    Route::get('/inloggen', 'showLoginChoice')->name('login.choice');
    Route::get('/inloggen/werkzoekende', 'showWerkzoekendeLogin')->name('login.werkzoekende');
    Route::post('/inloggen/werkzoekende', 'login')->defaults('role', User::ROLE_WERKZOEKENDE)->name('login.werkzoekende.submit');
    Route::get('/inloggen/werkgever', 'showWerkgeverLogin')->name('login.werkgever');
    Route::post('/inloggen/werkgever', 'login')->defaults('role', User::ROLE_WERKGEVER)->name('login.werkgever.submit');

    Route::get('/aanmelden', 'showRegisterChoice')->name('register.choice');
    Route::get('/aanmelden/werkzoekende', 'showWerkzoekendeRegister')->name('register.werkzoekende');
    Route::post('/aanmelden/werkzoekende', 'register')->defaults('role', User::ROLE_WERKZOEKENDE)->name('register.werkzoekende.submit');
    Route::get('/aanmelden/werkgever', 'showWerkgeverRegister')->name('register.werkgever');
    Route::post('/aanmelden/werkgever', 'register')->defaults('role', User::ROLE_WERKGEVER)->name('register.werkgever.submit');

    Route::get('/admin/inloggen', 'showAdminLogin')->name('admin.login');
    Route::post('/admin/inloggen', 'login')->defaults('role', User::ROLE_ADMIN)->name('admin.login.submit');

    Route::post('/uitloggen', 'logout')->middleware('auth')->name('logout');

    Route::redirect('/werkzoekende/dashboard', '/dashboard/werkzoekende');
    Route::redirect('/werkgever/dashboard', '/dashboard/werkgever');

    Route::get('/dashboard/werkzoekende', 'werkzoekendeDashboard')->middleware(['auth', 'role:werkzoekende'])->name('werkzoekende.dashboard');
    Route::get('/dashboard/werkgever', 'werkgeverDashboard')->middleware(['auth', 'role:werkgever'])->name('werkgever.dashboard');
    Route::get('/admin/dashboard', 'dashboard')->middleware(['auth', 'role:admin'])->name('admin.dashboard');
});
