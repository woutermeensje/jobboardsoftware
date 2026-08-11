<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_the_welcome_page_filters_jobs_by_search_query(): void
    {
        $response = $this->get('/?search=Laravel');

        $response
            ->assertStatus(200)
            ->assertSee('Laravel Developer')
            ->assertDontSee('Recruitment Marketeer');
    }

    public function test_the_public_navigation_has_role_submenus(): void
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee('Job alerts')
            ->assertSee('Nieuwsbrief')
            ->assertSee('/job-alerts', false)
            ->assertSee('/nieuwsbrief', false)
            ->assertSee('/vacature-plaatsen', false)
            ->assertSee('/tarieven', false)
            ->assertSee('/aanmelden/werkzoekende', false)
            ->assertSee('/aanmelden/werkgever', false);
    }

    public function test_public_menu_pages_are_available(): void
    {
        foreach ([
            '/werkzoekende',
            '/job-alerts',
            '/nieuwsbrief',
            '/werkgever',
            '/vacature-plaatsen',
            '/tarieven',
            '/over-ons',
            '/contact',
        ] as $path) {
            $this->get($path)->assertStatus(200);
            $this->get($path.'/')->assertStatus(200);
        }
    }

    public function test_auth_pages_are_available(): void
    {
        foreach ([
            '/inloggen',
            '/inloggen/werkzoekende',
            '/inloggen/werkgever',
            '/aanmelden',
            '/aanmelden/werkzoekende',
            '/aanmelden/werkgever',
            '/admin/inloggen',
        ] as $path) {
            $this->get($path)->assertStatus(200);
        }
    }

    public function test_werkzoekende_can_register_and_reaches_dashboard(): void
    {
        $response = $this->post('/aanmelden/werkzoekende', [
            'name' => 'Nieuwe Werkzoekende',
            'email' => 'werkzoekende@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('werkzoekende.dashboard'));
        $this->assertSame('/dashboard/werkzoekende', parse_url(route('werkzoekende.dashboard'), PHP_URL_PATH));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'werkzoekende@example.com',
            'role' => User::ROLE_WERKZOEKENDE,
        ]);

        $this->get('/dashboard/werkzoekende')
            ->assertStatus(200)
            ->assertSee('Werkzoekende omgeving')
            ->assertSee('Aanbevolen vacatures');

        $this->get('/dashboard/werkzoekende/')->assertStatus(200);
    }

    public function test_werkgever_can_register_and_reaches_dashboard(): void
    {
        $response = $this->post('/aanmelden/werkgever', [
            'name' => 'Nieuwe Werkgever',
            'company_name' => 'Hire Labs',
            'email' => 'werkgever@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('werkgever.dashboard'));
        $this->assertSame('/dashboard/werkgever', parse_url(route('werkgever.dashboard'), PHP_URL_PATH));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'werkgever@example.com',
            'company_name' => 'Hire Labs',
            'role' => User::ROLE_WERKGEVER,
        ]);

        $this->get('/dashboard/werkgever')
            ->assertStatus(200)
            ->assertSee('Werkgever omgeving')
            ->assertSee('Vacatures');

        $this->get('/dashboard/werkgever/')->assertStatus(200);
    }

    public function test_dashboard_routes_are_role_protected(): void
    {
        $werkzoekende = User::factory()->create([
            'role' => User::ROLE_WERKZOEKENDE,
        ]);

        $werkgever = User::factory()->create([
            'role' => User::ROLE_WERKGEVER,
        ]);

        $this->get('/dashboard/werkzoekende')->assertRedirect(route('login.choice'));
        $this->actingAs($werkzoekende)->get('/dashboard/werkgever')->assertForbidden();
        $this->actingAs($werkgever)->get('/dashboard/werkgever')->assertStatus(200);
    }

    public function test_old_dashboard_paths_redirect_to_new_dashboard_structure(): void
    {
        $this->get('/werkzoekende/dashboard')->assertRedirect('/dashboard/werkzoekende');
        $this->get('/werkgever/dashboard')->assertRedirect('/dashboard/werkgever');
    }

    public function test_admin_can_login_and_reaches_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Wouter',
            'email' => 'wouter@inhuren.com',
            'password' => 'AdminPassword123!',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post('/admin/inloggen', [
            'email' => 'wouter@inhuren.com',
            'password' => 'AdminPassword123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin/dashboard')->assertStatus(200)->assertSee('Admin dashboard');
    }
}
