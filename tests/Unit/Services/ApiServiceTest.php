<?php

namespace Tests\Unit\Services;

use App\Services\ApiService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;

class ApiServiceTest extends TestCase
{
    /** @test */
    public function it_fetches_quote_from_api()
    {
        // Mocking dependencies
        $baseUrl = 'http://example.com/api/';
        $httpClient = $this->createMock(Client::class);
        $response = new Response(200, [], '{"quote": "Sample quote"}');
        $httpClient->method('request')->willReturn($response);

        // Injecting mock client into ApiService instance
        $apiService = new ApiService($httpClient);

        // Performing the test
        $quote = $apiService->get();

        $this->assertEquals('Sample quote', $quote);
    }

    /** @test */
    public function it_logs_critical_error_on_api_failure()
    {
        // Mocking dependencies
        $baseUrl = 'http://example.com/api/';
        $httpClient = $this->createMock(Client::class);
        $exception = new RequestException("Error communicating with the server", new \GuzzleHttp\Psr7\Request('GET', $baseUrl));
        $httpClient->method('request')->willThrowException($exception);

        // Mocking the Log facade
        Log::shouldReceive('critical')->once();

        // Injecting mock client into ApiService instance
        $apiService = new ApiService($httpClient);

        // Performing the test
        $this->expectException(\Exception::class);
        $apiService->get();
    }
}
