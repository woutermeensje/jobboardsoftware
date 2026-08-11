<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_the_homepage_is_a_saas_marketing_page(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('SaaS job board software')
            ->assertSee('Job board software voor je eigen vacatureplatform')
            ->assertSee('Start gratis')
            ->assertDontSee('Laravel Developer');
    }

    public function test_the_public_navigation_targets_the_saas_product(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Product')
            ->assertSee('Features')
            ->assertSee('Eigen domein koppelen')
            ->assertSee('/aanmelden', false)
            ->assertSee('/inloggen', false)
            ->assertDontSee('/aanmelden/werkgever', false)
            ->assertDontSee('/aanmelden/werkzoekende', false);
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

    public function test_tenant_domain_renders_tenant_frontend(): void
    {
        $tenant = Tenant::create([
            'id' => 'acme',
            'name' => 'Acme Careers',
            'slug' => 'acme-careers',
            'plan' => Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => [
                'brand_name' => 'Acme Careers',
                'accent_color' => '#2f5f80',
            ],
        ]);

        $tenant->domains()->create([
            'domain' => 'acme.test',
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
        ]);

        $this->get('http://acme.test/')
            ->assertStatus(200)
            ->assertSee('Acme Careers')
            ->assertSee('Openstaande functies')
            ->assertSee('Laravel Developer');
    }

    public function test_saas_user_can_create_tenant_environment_with_domain(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
            'company_name' => 'Hire Labs',
        ]);

        $response = $this->actingAs($owner)->post('/dashboard/omgeving', [
            'name' => 'Acme Careers',
            'slug' => 'acme-careers',
            'domain' => 'https://vacatures.acme.test/jobs',
        ]);

        $response->assertRedirect(route('tenant.environments.index'));

        $this->assertDatabaseHas('tenants', [
            'id' => 'acme-careers',
            'owner_user_id' => $owner->id,
            'name' => 'Acme Careers',
            'slug' => 'acme-careers',
            'plan' => Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_TRIAL,
        ]);

        $this->assertDatabaseHas('domains', [
            'tenant_id' => 'acme-careers',
            'domain' => 'vacatures.acme.test',
            'is_primary' => true,
            'status' => Domain::STATUS_PENDING,
            'ssl_status' => Domain::SSL_PENDING,
        ]);

        $this->actingAs($owner)
            ->get('/dashboard/omgeving')
            ->assertStatus(200)
            ->assertSee('Acme Careers')
            ->assertSee('vacatures.acme.test');
    }

    public function test_auth_pages_are_available(): void
    {
        foreach ([
            '/inloggen',
            '/aanmelden',
            '/admin/inloggen',
        ] as $path) {
            $this->get($path)->assertStatus(200);
        }

        $this->get('/inloggen/werkzoekende')->assertRedirect('/inloggen');
        $this->get('/inloggen/werkgever')->assertRedirect('/inloggen');
        $this->get('/aanmelden/werkzoekende')->assertRedirect('/aanmelden');
        $this->get('/aanmelden/werkgever')->assertRedirect('/aanmelden');
    }

    public function test_saas_user_can_register_and_reaches_dashboard(): void
    {
        $response = $this->post('/aanmelden', [
            'name' => 'Nieuwe SaaS gebruiker',
            'company_name' => 'Hire Labs',
            'email' => 'owner@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('tenant.owner.dashboard'));
        $this->assertSame('/dashboard', parse_url(route('tenant.owner.dashboard'), PHP_URL_PATH));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'owner@example.com',
            'company_name' => 'Hire Labs',
            'role' => User::ROLE_TENANT_OWNER,
        ]);

        $this->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('SaaS beheeromgeving')
            ->assertSee('Mijn jobboards');
    }

    public function test_saas_user_can_login_and_reaches_dashboard(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner-login@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TENANT_OWNER,
        ]);

        $response = $this->post('/inloggen', [
            'email' => 'owner-login@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('tenant.owner.dashboard'));
        $this->assertAuthenticatedAs($owner);
    }

    public function test_dashboard_routes_are_role_protected(): void
    {
        $werkzoekende = User::factory()->create([
            'role' => User::ROLE_WERKZOEKENDE,
        ]);

        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
        ]);

        $this->get('/dashboard')->assertRedirect(route('login.choice'));
        $this->actingAs($werkzoekende)->get('/dashboard')->assertForbidden();
        $this->actingAs($owner)->get('/dashboard')->assertStatus(200);
    }

    public function test_old_dashboard_paths_redirect_to_the_saas_dashboard(): void
    {
        $this->get('/werkzoekende/dashboard')->assertRedirect('/dashboard');
        $this->get('/werkgever/dashboard')->assertRedirect('/dashboard');
        $this->get('/dashboard/werkgever')->assertRedirect('/dashboard');
        $this->get('/dashboard/werkgever/omgeving')->assertRedirect('/dashboard/omgeving');
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
