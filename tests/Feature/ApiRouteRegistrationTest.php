<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRouteRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_routes_are_loaded_with_the_api_prefix(): void
    {
        $this->getJson('/api/orders/count')
            ->assertOk()
            ->assertExactJson(['count' => 0]);

        $this->getJson('/api/pizarron/activity')
            ->assertOk()
            ->assertJsonStructure(['minutes', 'counts', 'recent']);
    }
}
