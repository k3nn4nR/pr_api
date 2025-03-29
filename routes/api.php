<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'login'])
    ->middleware('guest')
    ->name('login');


    
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',[App\Http\Controllers\Auth\AuthenticatedSessionController::class,'logout'])->name('api_logout');
});