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
        return view('auth.login-choice');
    }

    public function showRegisterChoice(): View
    {
        return view('auth.register-choice');
    }

    public function showWerkzoekendeLogin(): View
    {
        return view('auth.login', [
            'role' => User::ROLE_WERKZOEKENDE,
            'title' => 'Inloggen als werkzoekende',
            'subtitle' => 'Bekijk vacatures, bewaar interessante functies en beheer straks je sollicitaties.',
            'action' => route('login.werkzoekende.submit'),
            'registerUrl' => route('register.werkzoekende'),
        ]);
    }

    public function showWerkgeverLogin(): View
    {
        return view('auth.login', [
            'role' => User::ROLE_WERKGEVER,
            'title' => 'Inloggen als werkgever',
            'subtitle' => 'Plaats vacatures, beheer bedrijfspagina\'s en volg reacties vanuit je werkgeversomgeving.',
            'action' => route('login.werkgever.submit'),
            'registerUrl' => route('register.werkgever'),
        ]);
    }

    public function showAdminLogin(): View
    {
        return view('auth.login', [
            'role' => User::ROLE_ADMIN,
            'title' => 'Admin inloggen',
            'subtitle' => 'Beheer gebruikers, werkgevers, vacatures en platforminstellingen.',
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
        return view('auth.register', [
            'role' => User::ROLE_WERKZOEKENDE,
            'title' => 'Aanmelden als werkzoekende',
            'subtitle' => 'Maak een profiel aan om vacatures te bewaren en straks direct te solliciteren.',
            'action' => route('register.werkzoekende.submit'),
            'loginUrl' => route('login.werkzoekende'),
        ]);
    }

    public function showWerkgeverRegister(): View
    {
        return view('auth.register', [
            'role' => User::ROLE_WERKGEVER,
            'title' => 'Aanmelden als werkgever',
            'subtitle' => 'Maak een werkgeversaccount aan om vacatures te publiceren en kandidaten te beheren.',
            'action' => route('register.werkgever.submit'),
            'loginUrl' => route('login.werkgever'),
        ]);
    }

    public function register(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, [User::ROLE_WERKZOEKENDE, User::ROLE_WERKGEVER], true), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => [$role === User::ROLE_WERKGEVER ? 'required' : 'nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'company_name' => $role === User::ROLE_WERKGEVER ? $validated['company_name'] : null,
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $role,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

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
            User::ROLE_WERKGEVER => 'werkgever.dashboard',
            default => 'werkzoekende.dashboard',
        };
    }
}
