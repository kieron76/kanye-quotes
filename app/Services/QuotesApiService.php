<?php

namespace App\Services;

use App\Interfaces\QuotesApiServiceInterface;
use App\Services\ApiService;
use Illuminate\Redis\RedisManager;

class QuotesApiService implements QuotesApiServiceInterface
{
    private const QUOTE_LIST_KEY = 'quotes_list';

    public function __construct(
        private ApiService $apiService,
        private RedisManager $redisManager,
        private int $numberOfQuotes
    ) {
    }

    public function fetchQuotes(int $page = 0): array
    {
        $results = $this->fetchCachedQuotes($page);

        if ($results) {
            return $results;
        }

        $results = [];
        $i = 1;
        while ($i <= $this->numberOfQuotes) {
            $quote = $this->apiService->get();
            $hashedQuote = $this->hashQuote($quote);

            if (!$this->redisManager->exists($hashedQuote)) {
                $this->redisManager->rpush(self::QUOTE_LIST_KEY, $hashedQuote);
                $results['quote ' . $i] = $quote;
                $i++;
            }
        }

        return $results;
    }

    protected function fetchCachedQuotes(int $page): array|bool
    {
        $start = $this->getStartIndex($page);
        $end = $this->getEndIndex($page);

        $keys = $this->redisManager->lrange(self::QUOTE_LIST_KEY, $start, $end);

        if (empty($keys)) {
            return false;
        }

        $quotes = [];
        foreach ($keys as $index => $key) {
            $quotes['quote ' . ($index + 1)] = $this->redisManager->get($key);
        }

        return $quotes;
    }

    protected function hashQuote(string $quote): string
    {
        return hash('sha256', $quote);
    }

    protected function getStartIndex(int $page): int
    {
        return $page * $this->numberOfQuotes;
    }

    protected function getEndIndex(int $page): int
    {
        return $this->getStartIndex($page) + $this->numberOfQuotes - 1;
    }
}
