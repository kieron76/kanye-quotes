<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Interfaces\QuotesApiServiceInterface;

class QuoteController extends Controller
{
    public function __construct(
        private QuotesApiServiceInterface $quotesApiService,
    ) {
    }

    public function get(): JsonResponse
    {
        return new JsonResponse($this->quotesApiService->fetchQuotes());
    }

    public function refresh($page = 0): JsonResponse
    {
        return new JsonResponse($this->quotesApiService->fetchQuotes($page));
    }
}
