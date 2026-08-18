<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BillingPlan;
use App\Models\User;
use App\Support\AdminActionNotifier;
use App\Support\BillingPlanCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PortalAuthController extends Controller
{
    public function showLoginChoice(): View
    {
        return view('auth.login', [
            'role' => User::ROLE_TENANT_OWNER,
            'eyebrow' => 'SaaS account',
            'title' => 'Log in to JobBoardSoftware',
            'subtitle' => 'Log in to manage your job board, domains, license and settings.',
            'action' => route('login.submit'),
            'registerUrl' => route('register.choice'),
        ]);
    }

    public function showRegisterChoice(): View
    {
        return view('auth.register', [
            'role' => User::ROLE_TENANT_OWNER,
            'eyebrow' => 'SaaS account',
            'title' => 'Start your own job board',
            'formTitle' => 'Create account',
            'subtitle' => 'Create your account first. After verifying your email, you can finish your company, plan and payment setup.',
            'action' => route('register.submit'),
            'loginUrl' => route('login.choice'),
        ]);
    }

    public function showTenantLoginChoice(): View
    {
        $brandName = $this->tenantBrandName();

        return view('auth.login-choice', [
            'layout' => 'layouts.tenant',
            'tenant' => tenant(),
            'brandName' => $brandName,
            'title' => 'Log in',
            'eyebrow' => 'Tenant account',
            'heading' => 'Log in to '.$brandName,
            'subtitle' => 'Choose how you want to continue.',
            'jobseekerUrl' => route('tenant.login.jobseeker'),
            'employerUrl' => route('tenant.login.employer'),
            'registerUrl' => route('tenant.register.choice'),
            'backUrl' => route('tenant.home'),
            'backLabel' => 'Back to job board',
        ]);
    }

    public function showTenantJobseekerLogin(): View
    {
        return $this->showTenantLoginFor(
            User::ROLE_JOBSEEKER,
            'Job seeker login',
            'Log in as a job seeker',
            route('tenant.login.jobseeker.submit'),
            route('tenant.register.jobseeker'),
        );
    }

    public function showTenantEmployerLogin(): View
    {
        return $this->showTenantLoginFor(
            User::ROLE_EMPLOYER,
            'Employer login',
            'Log in as an employer',
            route('tenant.login.employer.submit'),
            route('tenant.register.employer'),
        );
    }

    public function showAdminLogin(): View
    {
        return view('auth.login', [
            'role' => User::ROLE_ADMIN,
            'title' => 'Admin login',
            'subtitle' => 'Manage SaaS users, tenants, domains and platform settings.',
            'action' => route('admin.login.submit'),
            'registerUrl' => null,
        ]);
    }

    public function login(Request $request, string $role): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $role,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These login credentials do not match this portal.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        if ($role === User::ROLE_TENANT_OWNER && $user instanceof User) {
            if (! $user->hasVerifiedEmail()) {
                $intendedPath = parse_url((string) $request->session()->get('url.intended'), PHP_URL_PATH);

                if (is_string($intendedPath) && str_starts_with($intendedPath, '/email/verify/')) {
                    return redirect()->intended(route('verification.notice'));
                }

                return redirect()->route('verification.notice');
            }

            if ($this->shouldResumeSaasOnboarding($user)) {
                return redirect()->route('register.onboarding');
            }
        }

        return redirect()->intended($this->dashboardRouteFor($role));
    }

    public function showTenantRegisterChoice(): View
    {
        $brandName = $this->tenantBrandName();

        return view('auth.register-choice', [
            'layout' => 'layouts.tenant',
            'tenant' => tenant(),
            'brandName' => $brandName,
            'title' => 'Sign up',
            'eyebrow' => 'Tenant account',
            'heading' => 'Create an account for '.$brandName,
            'subtitle' => 'Choose the account type that fits how you use this job board.',
            'jobseekerUrl' => route('tenant.register.jobseeker'),
            'employerUrl' => route('tenant.register.employer'),
            'loginUrl' => route('tenant.login.choice'),
            'backUrl' => route('tenant.home'),
            'backLabel' => 'Back to job board',
        ]);
    }

    public function showTenantJobseekerRegister(): View
    {
        return $this->showTenantRegisterFor(
            User::ROLE_JOBSEEKER,
            'Create job seeker account',
            'Create a job seeker account',
            route('tenant.register.jobseeker.submit'),
            route('tenant.login.jobseeker'),
        );
    }

    public function showTenantEmployerRegister(): View
    {
        return $this->showTenantRegisterFor(
            User::ROLE_EMPLOYER,
            'Create employer account',
            'Create an employer account',
            route('tenant.register.employer.submit'),
            route('tenant.login.employer'),
        );
    }

    public function register(Request $request, string $role): RedirectResponse
    {
        abort_unless($role === User::ROLE_TENANT_OWNER, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($query) => $query->whereNull('tenant_id')),
            ],
            'phone_number' => ['required', 'string', 'max:40'],
            'heard_about_us' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $name = trim($validated['first_name'].' '.$validated['last_name']);

        $user = User::create([
            'name' => $name,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'heard_about_us' => $validated['heard_about_us'],
            'password' => $validated['password'],
            'role' => $role,
            'billing_status' => 'trial',
            'onboarding_step' => 'plan',
        ]);

        app(AdminActionNotifier::class)->notify('New user registered', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone_number,
            'company' => 'Not completed yet',
            'role' => $user->role,
            'plan' => 'Not selected yet',
            'source' => $user->heard_about_us,
        ], $user);

        $user->sendEmailVerificationNotification();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function showSaasOnboarding(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $this->shouldResumeSaasOnboarding($user)) {
            return redirect()->route('client.dashboard');
        }

        $step = $this->currentSaasOnboardingStep($user);
        $plans = $this->activeBillingPlans();

        return view('auth.onboarding', [
            'title' => 'Finish sign up',
            'user' => $user,
            'step' => $step,
            'plans' => $plans,
            'selectedPlan' => $user->billingPlan,
            'action' => route('register.onboarding.update'),
        ]);
    }

    public function updateSaasOnboarding(Request $request): RedirectResponse
    {
        $user = $request->user();
        $currentStep = $this->currentSaasOnboardingStep($user);
        $step = (string) $request->input('step', $currentStep);

        if ($step !== $currentStep) {
            return redirect()->route('register.onboarding');
        }

        if ($step === 'company') {
            $validated = $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'company_name' => ['required', 'string', 'max:255'],
                'phone_number' => ['required', 'string', 'max:40'],
            ]);

            $user->update([
                'name' => trim($validated['first_name'].' '.$validated['last_name']),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'company_name' => $validated['company_name'],
                'phone_number' => $validated['phone_number'],
                'onboarding_step' => 'plan',
            ]);

            return redirect()->route('register.onboarding');
        }

        if ($step === 'plan') {
            $validated = $request->validate([
                'billing_plan_id' => [
                    'required',
                    Rule::exists('billing_plans', 'id')->where(fn ($query) => $query->where('is_active', true)),
                ],
            ]);

            $user->update([
                'billing_plan_id' => $validated['billing_plan_id'],
                'billing_status' => $user->billing_status ?: 'trial',
                'onboarding_step' => 'billing',
            ]);

            return redirect()->route('register.onboarding');
        }

        return redirect()->route('billing.checkout');
    }

    public function tenantLogin(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, [User::ROLE_JOBSEEKER, User::ROLE_EMPLOYER], true), 404);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $role,
            'tenant_id' => tenant('id'),
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These login credentials do not match this job board.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route($this->tenantDashboardRouteNameFor($role)));
    }

    public function tenantRegister(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, [User::ROLE_JOBSEEKER, User::ROLE_EMPLOYER], true), 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('tenant_id', tenant('id'))),
            ],
            'phone_number' => ['required', 'string', 'max:40'],
            'company_name' => [Rule::requiredIf($role === User::ROLE_EMPLOYER), 'nullable', 'string', 'max:255'],
            'heard_about_us' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $name = trim($validated['first_name'].' '.$validated['last_name']);

        $user = User::create([
            'tenant_id' => tenant('id'),
            'name' => $name,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'company_name' => $validated['company_name'] ?? null,
            'heard_about_us' => $validated['heard_about_us'],
            'password' => $validated['password'],
            'role' => $role,
        ]);

        app(AdminActionNotifier::class)->notify('New tenant user registered', [
            'tenant_id' => tenant('id'),
            'tenant_name' => tenant('name'),
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone_number,
            'company' => $user->company_name,
            'role' => $user->role,
            'source' => $user->heard_about_us,
        ], $user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($this->tenantDashboardRouteNameFor($role));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.choice');
    }

    public function tenantLogout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login.choice');
    }

    public function dashboard(Request $request): View
    {
        return view('auth.dashboard', [
            'user' => $request->user(),
        ]);
    }

    public function werkzoekendeDashboard(Request $request): View
    {
        return view('dashboard.werkzoekende', [
            'user' => $request->user(),
        ]);
    }

    private function dashboardRouteFor(string $role): string
    {
        return route($this->dashboardRouteNameFor($role));
    }

    private function dashboardRouteNameFor(string $role): string
    {
        return match ($role) {
            User::ROLE_ADMIN => 'admin.dashboard',
            User::ROLE_TENANT_OWNER => 'client.dashboard',
            default => 'werkzoekende.dashboard',
        };
    }

    private function tenantDashboardRouteNameFor(string $role): string
    {
        return match ($role) {
            User::ROLE_EMPLOYER => 'tenant.employer.dashboard',
            default => 'tenant.jobseeker.dashboard',
        };
    }

    private function showTenantLoginFor(string $role, string $title, string $formTitle, string $action, string $registerUrl): View
    {
        return view('auth.login', [
            'layout' => 'layouts.tenant',
            'tenant' => tenant(),
            'brandName' => $this->tenantBrandName(),
            'role' => $role,
            'title' => $title,
            'formTitle' => $formTitle,
            'subtitle' => 'Access your account for this job board.',
            'action' => $action,
            'registerUrl' => $registerUrl,
            'backUrl' => route('tenant.home'),
            'backLabel' => 'Back to job board',
        ]);
    }

    private function showTenantRegisterFor(string $role, string $title, string $formTitle, string $action, string $loginUrl): View
    {
        return view('auth.register', [
            'layout' => 'layouts.tenant',
            'tenant' => tenant(),
            'brandName' => $this->tenantBrandName(),
            'role' => $role,
            'title' => $title,
            'formTitle' => $formTitle,
            'subtitle' => 'This account belongs only to this job board.',
            'action' => $action,
            'loginUrl' => $loginUrl,
            'backUrl' => route('tenant.home'),
            'backLabel' => 'Back to job board',
            'requiresCompanyName' => $role === User::ROLE_EMPLOYER,
        ]);
    }

    private function tenantBrandName(): string
    {
        $tenant = tenant();
        $settings = $tenant?->settings ?? [];

        return $settings['brand_name'] ?? $tenant?->name ?? 'Jobboard';
    }

    private function currentSaasOnboardingStep(User $user): string
    {
        if (! filled($user->company_name)) {
            return 'company';
        }

        if (! $user->billingPlan instanceof BillingPlan || ! $user->billingPlan->is_active) {
            return 'plan';
        }

        return 'payment';
    }

    private function shouldResumeSaasOnboarding(User $user): bool
    {
        return $this->currentSaasOnboardingStep($user) !== 'payment'
            || $user->onboarding_step === 'billing';
    }

    private function activeBillingPlans()
    {
        $sortOrder = array_flip(BillingPlanCatalog::sortOrder());

        return BillingPlan::query()
            ->where('is_active', true)
            ->orderBy('monthly_price_cents')
            ->get()
            ->sortBy(fn (BillingPlan $plan): int => (($sortOrder[$plan->key] ?? 99) * 100000000) + $plan->monthly_price_cents)
            ->values();
    }
}
