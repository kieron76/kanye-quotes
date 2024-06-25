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
            'quote 3' => 'Cached Quote 3',
            'quote 4' => 'Cached Quote 4',
            'quote 5' => 'Cached Quote 5',
        ];

        $this->redisManager->shouldReceive('lrange')
            ->andReturn([
                'quote 1',
                'quote 2',
                'quote 3',
                'quote 4',
                'quote 5',
            ]);

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
            'Quote 3 from API',
            'Quote 4 from API',
            'Quote 5 from API',
        );

        $this->redisManager->shouldReceive('lrange')
            ->andReturn(false);

        $this->redisManager->shouldReceive('exists')
            ->times($this->numberOfQuotes)
            ->andReturn(false);

        $this->redisManager->shouldReceive('rpush')
            ->times($this->numberOfQuotes);

        $quotes = $service->fetchQuotes();

        $this->assertCount($this->numberOfQuotes, $quotes);
        $this->assertArrayHasKey('quote 1', $quotes);
        $this->assertArrayHasKey('quote 2', $quotes);
        $this->assertArrayHasKey('quote 3', $quotes);
        $this->assertArrayHasKey('quote 4', $quotes);
        $this->assertArrayHasKey('quote 5', $quotes);
    }
}
