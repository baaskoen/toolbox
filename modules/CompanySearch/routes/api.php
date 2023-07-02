<?php

use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;
use Modules\CompanySearch\Controllers\CompanySearchController;

Route::group(['middleware' => [AuthenticateApiToken::class, 'api'], 'prefix' => 'api'], function() {
    Route::GET('companies', [CompanySearchController::class, 'search'])
        ->name('company.search');

    Route::GET('companies/{slug}', [CompanySearchController::class, 'details'])
        ->name('company.detail');
});
