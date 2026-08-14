<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_filament_login_page_is_available(): void
    {
        $this->get('/filament/login')->assertOk();
    }

    public function test_admin_user_can_access_filament_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get('/filament')
            ->assertOk()
            ->assertSee('Users')
            ->assertSee('Environments')
            ->assertSee('Applications');
    }

    public function test_admin_user_can_open_filament_resource_indexes(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        foreach ([
            '/filament/users',
            '/filament/tenants',
            '/filament/domains',
            '/filament/tenant-jobs',
            '/filament/job-applications',
            '/filament/billing-plans',
        ] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }
}
