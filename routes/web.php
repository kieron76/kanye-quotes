<?php

use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\QuoteController;

Route::get('/refresh/{page?}', [QuoteController::class, 'refresh'])
    ->middleware('app-middlewear');

Route::get('/', [QuoteController::class, 'get'])
    ->middleware('app-middlewear');
