<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ApiService;
use App\Services\QuotesApiService;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function () {
            return new Client([
                'base_uri' => config('app.kanye_api'),
            ]);
        });

        $this->app->when(QuotesApiService::class)
            ->needs('$redisPrefix')
            ->give(config('database.redis.options.prefix', 'quotes'));

        $this->app->when(QuotesApiService::class)
            ->needs('$numberOfQuotes')
            ->give(config('app.quote_count'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
