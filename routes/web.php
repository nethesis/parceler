<?php

use App\Http\Controllers\RepositoryController;
use App\Http\Middleware\ForceBasicAuth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/repository/{type}/{repository:name}/{path}', RepositoryController::class)
    ->whereIn('type', array_keys(config('repositories.endpoints')))
    ->where('path', '.*')
    ->middleware(ForceBasicAuth::class);
