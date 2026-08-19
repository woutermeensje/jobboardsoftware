<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\PortalAuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\ContactController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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
    Route::view('/guide', 'pages.guide')->name('pages.guide');
    Route::view('/how-to-start-a-job-board', 'pages.how-to-start-a-job-board')->name('pages.how-to-start-a-job-board');
    Route::view('/guide/choosing-a-niche-for-your-job-board', 'pages.guides.choosing-a-niche-for-your-job-board')->name('pages.guides.choosing-a-niche-for-your-job-board');
    Route::view('/guide/building-an-audience-and-generating-traffic', 'pages.guides.building-an-audience-and-generating-traffic')->name('pages.guides.building-an-audience-and-generating-traffic');
    Route::view('/guide/generating-recurring-customers', 'pages.guides.generating-recurring-customers')->name('pages.guides.generating-recurring-customers');
    Route::view('/guide/run-your-job-board-as-an-agency', 'pages.guides.run-your-job-board-as-an-agency')->name('pages.guides.run-your-job-board-as-an-agency');
    Route::view('/guide/give-deals-to-your-customers', 'pages.guides.give-deals-to-your-customers')->name('pages.guides.give-deals-to-your-customers');
    Route::view('/guide/how-to-price-your-job-postings', 'pages.guides.how-to-price-your-job-postings')->name('pages.guides.how-to-price-your-job-postings');
    Route::view('/guide/how-to-do-seo-geo-for-your-job-board', 'pages.guides.how-to-do-seo-geo-for-your-job-board')->name('pages.guides.how-to-do-seo-geo-for-your-job-board');
    Route::view('/guide/the-importance-of-job-category-pages', 'pages.guides.the-importance-of-job-category-pages')->name('pages.guides.the-importance-of-job-category-pages');
    Route::view('/guide/how-to-get-the-right-traffic', 'pages.guides.how-to-get-the-right-traffic')->name('pages.guides.how-to-get-the-right-traffic');
    Route::view('/guide/why-should-you-acquire-a-job-board', 'pages.guides.why-should-you-acquire-a-job-board')->name('pages.guides.why-should-you-acquire-a-job-board');
    Route::view('/guide/how-to-acquire-a-job-board', 'pages.guides.how-to-acquire-a-job-board')->name('pages.guides.how-to-acquire-a-job-board');
    Route::view('/guide/choosing-a-job-board-name', 'pages.guides.choosing-a-job-board-name')->name('pages.guides.choosing-a-job-board-name');
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
        Route::get('/sign-up/setup', 'showSaasOnboarding')
            ->middleware(['auth', 'role:tenant_owner', 'verified'])
            ->name('register.onboarding');
        Route::post('/sign-up/setup', 'updateSaasOnboarding')
            ->middleware(['auth', 'role:tenant_owner', 'verified'])
            ->name('register.onboarding.update');
        Route::redirect('/set-up', '/sign-up/setup');
        Route::redirect('/setup', '/sign-up/setup');
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

    Route::get('/email/verify', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('register.onboarding');
        }

        return view('auth.verify-email', [
            'title' => 'Verify your email',
            'user' => $request->user(),
        ]);
    })->middleware(['auth', 'role:tenant_owner'])->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()
            ->route('register.onboarding')
            ->with('status', 'Email verified. Finish the final sign up steps.');
    })->middleware(['auth', 'role:tenant_owner', 'signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('register.onboarding');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware(['auth', 'role:tenant_owner', 'throttle:6,1'])->name('verification.send');

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
            Route::get('/environments', [ClientDashboardController::class, 'environments'])->name('environments.index');
            Route::get('/environments/create', [ClientDashboardController::class, 'createEnvironment'])->name('environments.create');
            Route::post('/environments', [ClientDashboardController::class, 'storeEnvironment'])->name('environments.store');
            Route::post('/environments/{tenant}/activate', [ClientDashboardController::class, 'activateEnvironment'])->name('environments.activate');
            Route::delete('/environments/{tenant}', [ClientDashboardController::class, 'destroyEnvironment'])->name('environments.destroy');
            Route::get('/jobs', [ClientDashboardController::class, 'jobs'])->name('jobs.index');
            Route::post('/jobs', [ClientDashboardController::class, 'storeJob'])->name('jobs.store');
            Route::get('/jobs/create', [ClientDashboardController::class, 'createJob'])->name('jobs.create');
            Route::get('/jobs/{job}/edit', [ClientDashboardController::class, 'editJob'])->name('jobs.edit');
            Route::patch('/jobs/{job}', [ClientDashboardController::class, 'updateJob'])->name('jobs.update');
            Route::get('/applications', [ClientDashboardController::class, 'section'])->defaults('section', 'applications')->name('applications.index');
            Route::get('/domains', [ClientDashboardController::class, 'domains'])->name('domains.index');
            Route::post('/domains', [ClientDashboardController::class, 'storeDomain'])->name('domains.store');
            Route::get('/domains/create', [ClientDashboardController::class, 'domains'])->name('domains.create');
            Route::post('/domains/{domain}/verify', [ClientDashboardController::class, 'verifyDomain'])->name('domains.verify');
            Route::delete('/domains/{domain}', [ClientDashboardController::class, 'destroyDomain'])->name('domains.destroy');
            Route::get('/settings', [ClientDashboardController::class, 'settings'])->name('settings');
            Route::patch('/settings', [ClientDashboardController::class, 'updateSettings'])->name('settings.update');
            Route::get('/billing', [ClientDashboardController::class, 'section'])->defaults('section', 'billing')->name('billing');
            Route::get('/marketing', [ClientDashboardController::class, 'section'])->defaults('section', 'marketing')->name('marketing.index');
            Route::get('/marketing/landingpagina', [ClientDashboardController::class, 'landingPages'])->name('marketing.landingpagina');
            Route::get('/marketing/landingpagina/create', [ClientDashboardController::class, 'createLandingPage'])->name('marketing.landingpagina.create');
            Route::post('/marketing/landingpagina', [ClientDashboardController::class, 'storeLandingPage'])->name('marketing.landingpagina.store');
            Route::get('/marketing/landingpagina/{landingPage}/edit', [ClientDashboardController::class, 'editLandingPage'])->name('marketing.landingpagina.edit');
            Route::patch('/marketing/landingpagina/{landingPage}', [ClientDashboardController::class, 'updateLandingPage'])->name('marketing.landingpagina.update');
            Route::delete('/marketing/landingpagina/{landingPage}', [ClientDashboardController::class, 'destroyLandingPage'])->name('marketing.landingpagina.destroy');
            Route::get('/marketing/socials', [ClientDashboardController::class, 'section'])->defaults('section', 'socials')->name('marketing.socials');
            Route::get('/jobs-settings', [ClientDashboardController::class, 'section'])->defaults('section', 'jobs-settings')->name('jobs-settings.index');
            Route::get('/jobs-settings/sector', [ClientDashboardController::class, 'section'])->defaults('section', 'sector')->name('jobs-settings.sector');
            Route::post('/jobs-settings/sector', [ClientDashboardController::class, 'storeJobSettingOption'])->defaults('optionType', 'sector')->name('jobs-settings.sector.store');
            Route::get('/jobs-settings/categorie', [ClientDashboardController::class, 'section'])->defaults('section', 'categorie')->name('jobs-settings.categorie');
            Route::post('/jobs-settings/categorie', [ClientDashboardController::class, 'storeJobSettingOption'])->defaults('optionType', 'categorie')->name('jobs-settings.categorie.store');
            Route::get('/jobs-settings/job-type', [ClientDashboardController::class, 'jobTypes'])->name('jobs-settings.job-type');
            Route::post('/jobs-settings/job-type', [ClientDashboardController::class, 'storeJobType'])->name('jobs-settings.job-type.store');
            Route::get('/jobs-settings/organization-type', [ClientDashboardController::class, 'section'])->defaults('section', 'organization-type')->name('jobs-settings.organization-type');
            Route::post('/jobs-settings/organization-type', [ClientDashboardController::class, 'storeJobSettingOption'])->defaults('optionType', 'organization-type')->name('jobs-settings.organization-type.store');
            Route::get('/companies', [ClientDashboardController::class, 'companies'])->name('companies.index');
            Route::post('/companies', [ClientDashboardController::class, 'storeCompany'])->name('companies.store');
            Route::get('/companies/create', [ClientDashboardController::class, 'createCompany'])->name('companies.create');
            Route::get('/companies/{company}/edit', [ClientDashboardController::class, 'editCompany'])->name('companies.edit');
            Route::patch('/companies/{company}', [ClientDashboardController::class, 'updateCompany'])->name('companies.update');
            Route::get('/packages', [ClientDashboardController::class, 'packages'])->name('packages.index');
            Route::get('/packages/create', [ClientDashboardController::class, 'createPackage'])->name('packages.create');
            Route::post('/packages', [ClientDashboardController::class, 'storePackage'])->name('packages.store');
            Route::get('/packages/{package}/edit', [ClientDashboardController::class, 'editPackage'])->name('packages.edit');
            Route::patch('/packages/{package}', [ClientDashboardController::class, 'updatePackage'])->name('packages.update');
            Route::get('/newsletter-subscribers', [ClientDashboardController::class, 'newsletterSubscribers'])->name('newsletter-subscribers.index');
            Route::get('/job-alerts', [ClientDashboardController::class, 'jobAlerts'])->name('job-alerts.index');
        });

    Route::get('/dashboard/billing/checkout', [BillingController::class, 'checkout'])
        ->middleware(['auth', 'role:tenant_owner'])
        ->name('billing.checkout');

    Route::get('/dashboard/billing/success', [BillingController::class, 'success'])
        ->middleware(['auth', 'role:tenant_owner'])
        ->name('billing.success');
};

$centralDomains = config('tenancy.central_domains');

if ($centralDomains === []) {
    $centralRoutes();
} else {
    $defaultCentralDomain = parse_url((string) config('app.url'), PHP_URL_HOST);

    if (is_string($defaultCentralDomain) && in_array($defaultCentralDomain, $centralDomains, true)) {
        $centralDomains = array_values(array_unique([
            $defaultCentralDomain,
            ...array_filter($centralDomains, fn (string $domain): bool => $domain !== $defaultCentralDomain),
        ]));
    }

    foreach ($centralDomains as $index => $domain) {
        $route = Route::domain($domain);

        if ($index > 0) {
            $route->as('central.'.Str::slug($domain).'.');
        }

        $route->group($centralRoutes);
    }
}
