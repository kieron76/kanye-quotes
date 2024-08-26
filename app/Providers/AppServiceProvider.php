<?php

namespace App\Providers;

use App\Interfaces\QuotesApiServiceInterface;
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

        $this->app->singleton(QuotesApiServiceInterface::class, function () {
            return $this->app->make(QuotesApiService::class);
        });

        $this->app->when(QuotesApiService::class)
            ->needs('$numberOfQuotes')
            ->give(config('app.quote_count'));

        $this->app->when(QuotesApiService::class)
            ->needs('$prefix')
            ->give(config('database.redis.options.prefix'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
