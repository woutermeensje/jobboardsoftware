<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminActionNotifier;
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
            'subtitle' => 'Create an admin account to start your license and connect your own domain.',
            'action' => route('register.submit'),
            'loginUrl' => route('login.choice'),
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

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
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
        ]);

        app(AdminActionNotifier::class)->notify('Nieuwe gebruiker aangemeld', [
            'naam' => $user->name,
            'email' => $user->email,
            'telefoon' => $user->phone_number,
            'rol' => $user->role,
            'bron' => $user->heard_about_us,
        ], $user);

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
