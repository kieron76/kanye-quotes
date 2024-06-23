<?php

namespace Tests\Unit\Services;

use App\Services\QuotesApiService;
use App\Services\ApiService;
use Illuminate\Redis\RedisManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class QuoteApiServiceTest extends TestCase
{

    protected ApiService $apiService;
    protected RedisManager $redisManager;
    protected int $numberOfQuotes = 5;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiService = Mockery::mock(ApiService::class);
        $this->redisManager = Mockery::mock(RedisManager::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /** @test */
    public function fetchQuotes_returns_cached_quotes_if_available()
    {
        $service = new QuotesApiService($this->apiService, $this->redisManager, $this->numberOfQuotes);

        $cachedQuotes = [
            'quote 1' => 'Cached Quote 1',
            'quote 2' => 'Cached Quote 2',
        ];

        $this->redisManager->shouldReceive('scan')
            ->once()
            ->andReturn([0, array_keys($cachedQuotes)]);

        foreach ($cachedQuotes as $key => $quote) {
            $this->redisManager->shouldReceive('get')
                ->once()
                ->with($key)
                ->andReturn($quote);
        }

        $quotes = $service->fetchQuotes();

        $this->assertEquals($cachedQuotes, $quotes);
    }

    /** @test */
    public function fetchQuotes_fetches_new_quotes_when_not_cached()
    {
        $service = new QuotesApiService($this->apiService, $this->redisManager, $this->numberOfQuotes);

        $this->apiService->shouldReceive('get')->times($this->numberOfQuotes)->andReturn(
            'Quote 1 from API',
            'Quote 2 from API',
        );

        $this->redisManager->shouldReceive('scan')
            ->once()
            ->andReturn([0, []]);

        $this->redisManager->shouldReceive('exists')
            ->times($this->numberOfQuotes)
            ->andReturn(false);

        $this->redisManager
            ->shouldReceive('set')
            ->times($this->numberOfQuotes);

        $quotes = $service->fetchQuotes();

        $this->assertCount($this->numberOfQuotes, $quotes);
        $this->assertArrayHasKey('quote1', $quotes);
        $this->assertArrayHasKey('quote2', $quotes);
    }
}
