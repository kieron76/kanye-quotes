<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckConfig
{
    /**
     * Make sure that the application is configured correctly.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $apiToken = config('app.api_token');

        if (strlen(trim($apiToken)) < 10) {
            $logMessage = "The application has not been configured correctly, ";
            $logMessage .= "please ensure you set the api token and that it is not under 10 characters long.";

            Log::critical($logMessage);

            return response()->json(['error' => 'Service Unavailable'], 503);
        }

        $api = config('app.kanye_api');

        if (filter_var($api, FILTER_VALIDATE_URL) == false) {
            $logMessage = "The application has not been configured correctly, ";
            $logMessage .= "please ensure you set the api for this service to use.";

            Log::critical($logMessage);

            return response()->json(['error' => 'Service Unavailable'], 503);
        }

        return $next($request);
    }
}
