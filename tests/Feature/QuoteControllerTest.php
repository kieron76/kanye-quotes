<?php

namespace Tests\Feature;

use Tests\TestCase;

class QuoteControllerTest extends TestCase
{
    /** @test */
    public function it_rejects_calls_with_no_api_token()
    {
        $response = $this->get('/');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_quotes()
    {
        $response = $this->get('/', [
            'api-token' => config('app.api_token')
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    /** @test */
    public function it_refreshes_quotes()
    {
        $response = $this->get("/refresh", [
            'api-token' => config('app.api_token')
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    /** @test */
    public function it_refreshes_quotes_with_page()
    {
        $response = $this->get("/refresh/3", [
            'api-token' => config('app.api_token')
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }
}
