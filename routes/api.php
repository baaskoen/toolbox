<?php

use App\Http\Controllers\LibreController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => AuthenticateApiToken::class], function() {
    Route::POST('libre/convert-file', [LibreController::class, 'convert']);
});

