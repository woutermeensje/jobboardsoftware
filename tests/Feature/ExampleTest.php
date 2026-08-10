<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
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
}
