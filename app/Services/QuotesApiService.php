<?php

namespace App\Services;

use App\Interfaces\QuotesApiServiceInterface;
use App\Services\ApiService;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Redis;

class QuotesApiService implements QuotesApiServiceInterface
{
    public function __construct(
        private ApiService $apiService,
        private RedisManager $redisManager,
        private int $numberOfQuotes,
        private string $prefix,
    ) {
    }

    public function fetchQuotes(int $page = 1): array
    {
        // make sure the page number is greater than 1
        // could warn the consumer of this class that they are doing it wrong
        if ($page < 1) {
            $page = 1;
        }

        $results = $this->fetchCachedQuotes($page);

        // if there is cache, then just return those cached results
        if ($results) {
            return $results;
        }

        $results = [];
        $i = 1;
        while ($i <= $this->numberOfQuotes) {
            // get the quote from the api
            $quote = $this->apiService->get();

            // store the quote in cache with a key as a hash of the quote
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
        $keys = $this->redisManager->keys('*');

        // if no cache, or the requested page of quotes is greater than the cache store
        // then return false
        if (empty($keys) || count($keys) - 1 < $this->getStartIndex($page)) {
            return false;
        }

        $quotes = [];
        $returnIndex = 1;
        // skip the cache store until we get to the page of quotes we need
        for ($i = $this->getStartIndex($page); $i < $this->getEndIndex($page); $i++) {
            $keyWithoutPrefix = str_replace($this->prefix, '', $keys[$i]);
            $quotes['quote ' . ($returnIndex)] = $this->redisManager->get($keyWithoutPrefix);
            $returnIndex++;
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
