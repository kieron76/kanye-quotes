<?php

namespace App\Interfaces;

interface QuotesApiServiceInterface
{
    public function fetchQuotes(int $page = 0): array;
}
