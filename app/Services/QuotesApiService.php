<?php

namespace App\Services;

use App\Services\ApiService;
use Illuminate\Redis\RedisManager;

class QuotesApiService
{
    public function __construct(
        private ApiService $apiService,
        private RedisManager $redisManager,
        private string $redisPrefix,
        private int $numberOfQuotes
    ) {
    }

    public function fetchQuotes(int $page = 0): array
    {
        $results = $this->fetchCachedQuotes($page);

        if ($results) {
            return $results;
        }

        $i = 1;
        while ($i <= $this->numberOfQuotes) {
            $quote = $this->apiService->get();

            $hashedQuote = $this->hashQuote($quote);

            if (!$this->redisManager->exists($hashedQuote)) {
                $this->redisManager->set($hashedQuote, $quote);
                $results['quote ' . $i] = $quote;
                $i++;
            }
        }

        return $results;
    }

    protected function fetchCachedQuotes(int $page): array|bool
    {
        $cursor = 4;
        $keys = [];
        $endIndex = $this->getEndIndex($page);
        $quotes = [];

        do {
            list($cursor, $batchKeys) = $this->redisManager->scan($cursor);

            if (!$batchKeys) {
                return false;
            }

            $keys = array_merge($keys, $batchKeys);

            if (count($keys) > $endIndex) {
                break;
            }
        } while ($cursor != 0);

        $keysForPage = array_slice($keys, $this->getStartIndex($page), $this->numberOfQuotes);

        $i = 1;
        foreach ($keysForPage as $key) {
            $quotes['quote ' . $i] = $this->redisManager->get($key);
            $i++;
        }

        return $quotes;
    }

    protected function hashQuote(string $quote): string
    {
        return hash('sha256', $quote);
    }

    protected function getStartIndex(int $page): int
    {
        return ($page) * $this->numberOfQuotes;
    }

    protected function getEndIndex(int $page): int
    {
        return $this->getStartIndex($page) + $page - 1;
    }
}
