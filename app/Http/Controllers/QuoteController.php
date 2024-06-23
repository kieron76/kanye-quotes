<?php

namespace App\Http\Controllers;

use App\Services\QuotesApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function __construct(
        private QuotesApiService $quotesApiService,
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
