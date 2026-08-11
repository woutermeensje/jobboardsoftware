<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PortalAuthController extends Controller
{
    public function showLoginChoice(): View
    {
        return view('auth.login', [
            'role' => User::ROLE_TENANT_OWNER,
            'eyebrow' => 'SaaS account',
            'title' => 'Inloggen op JobBoardSoftware',
            'subtitle' => 'Log in om je jobboard, domeinen, licentie en instellingen te beheren.',
            'action' => route('login.submit'),
            'registerUrl' => route('register.choice'),
        ]);
    }

    public function showRegisterChoice(): View
    {
        return view('auth.register', [
            'role' => User::ROLE_TENANT_OWNER,
            'eyebrow' => 'SaaS account',
            'title' => 'Start je eigen jobboard',
            'subtitle' => 'Maak een beheeraccount aan om je licentie te starten en je eigen domein te koppelen.',
            'action' => route('register.submit'),
            'loginUrl' => route('login.choice'),
            'companyLabel' => 'Organisatie of label',
        ]);
    }

    public function showWerkzoekendeLogin(): View
    {
        return $this->showLoginChoice();
    }

    public function showWerkgeverLogin(): View
    {
        return $this->showLoginChoice();
    }

    public function showAdminLogin(): View
    {
        return view('auth.login', [
            'role' => User::ROLE_ADMIN,
            'title' => 'Admin inloggen',
            'subtitle' => 'Beheer SaaS gebruikers, tenants, domeinen en platforminstellingen.',
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
                ->withErrors(['email' => 'Deze inloggegevens kloppen niet voor deze omgeving.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->dashboardRouteFor($role));
    }

    public function showWerkzoekendeRegister(): View
    {
        return $this->showRegisterChoice();
    }

    public function showWerkgeverRegister(): View
    {
        return $this->showRegisterChoice();
    }

    public function register(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, [User::ROLE_WERKZOEKENDE, User::ROLE_WERKGEVER, User::ROLE_TENANT_OWNER], true), 404);

        $requiresCompanyName = in_array($role, [User::ROLE_WERKGEVER, User::ROLE_TENANT_OWNER], true);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => [$requiresCompanyName ? 'required' : 'nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'company_name' => $requiresCompanyName ? $validated['company_name'] : null,
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $role,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($role === User::ROLE_TENANT_OWNER) {
            return redirect()->route('onboarding.index');
        }

        return redirect()->route($this->dashboardRouteNameFor($role));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.choice');
    }

    public function dashboard(Request $request): View
    {
        return view('auth.dashboard', [
            'user' => $request->user(),
        ]);
    }

    public function werkgeverDashboard(Request $request): View
    {
        return view('dashboard.werkgever', [
            'user' => $request->user(),
            'tenants' => $request->user()->ownedTenants()->with('domains')->latest()->get(),
        ]);
    }

    public function tenantOwnerDashboard(Request $request): View
    {
        return view('dashboard.werkgever', [
            'user' => $request->user(),
            'tenants' => $request->user()->ownedTenants()->with('domains')->latest()->get(),
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
            User::ROLE_TENANT_OWNER, User::ROLE_WERKGEVER => 'tenant.owner.dashboard',
            default => 'werkzoekende.dashboard',
        };
    }
}
