<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login',[App\Http\Controllers\Auth\AuthenticatedSessionController::class,'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout',[App\Http\Controllers\Auth\AuthenticatedSessionController::class,'logout']);
    Route::resource('brand', App\Http\Controllers\BrandController::class);
    // Route::resource('status', App\Http\Controllers\Api\StatusController::class); 
    // Route::resource('code', App\Http\Controllers\Api\CodeController::class);
    // Route::resource('tag', App\Http\Controllers\Api\TagController::class);
    // Route::name('status.')->group(function () {
    //     Route::controller(App\Http\Controllers\Api\StatusController::class)->group(function () {
    //         Route::post('/status/{status}/tags','update_status_tags')->name('update_status_tags');
    //         Route::post('/status/{status}/codes','update_status_codes')->name('update_status_codes');
    //         Route::post('/status/{status}/statuses','update_status_statuses')->name('update_status_statuses');
    //         Route::post('/status/{status}/files','update_status_files')->name('update_status_files');
    //         Route::post('/status/{status}/comments','store_status_comment')->name('store_status_comment');
    //         Route::get('/status/{status}/codes','get_status_codes')->name('get_status_codes');
    //         Route::get('/status/{status}/statuses','get_status_statuses')->name('get_status_statuses');
    //         Route::get('/status/{status}/files','get_status_files')->name('get_status_files');
    //         Route::get('/status/{status}/comments','get_status_comments')->name('get_status_comments');
    //         Route::delete('/status/{status}/files/{file}','destroy_status_file')->name('destroy_status_file');
    //         Route::delete('/status/{status}/comments/{comment}','destroy_status_comment')->name('destroy_status_comment');
    //     });
    // });
    // Route::name('code.')->group(function () {
    //     Route::controller(App\Http\Controllers\Api\CodeController::class)->group(function () {
    //         Route::post('/code/{code}/tags','update_code_tags')->name('update_code_tags');
    //         Route::post('/code/{code}/codes','update_code_codes')->name('update_code_codes');
    //         Route::post('/code/{code}/statuses','update_code_statuses')->name('update_code_statuses');
    //         Route::post('/code/{code}/files','update_code_files')->name('update_code_files');
    //         Route::post('/code/{code}/comments','store_code_comment')->name('store_code_comment');
    //         Route::get('/code/{code}/codes','get_code_codes')->name('get_code_codes');
    //         Route::get('/code/{code}/tags','get_code_tags')->name('get_code_tags');
    //         Route::get('/code/{code}/statuses','get_code_statuses')->name('get_code_statuses');
    //         Route::get('/code/{code}/files','get_code_files')->name('get_code_files');
    //         Route::get('/code/{code}/comments','get_code_comments')->name('get_code_comments');
    //         Route::delete('/code/{code}/files/{file}','destroy_code_file')->name('destroy_code_file');
    //         Route::delete('/code/{code}/comments/{comment}','destroy_code_comment')->name('destroy_code_comment');
    //     });
    // });
    // Route::name('tag.')->group(function () {
    //     Route::controller(App\Http\Controllers\Api\TagController::class)->group(function () {
    //         Route::post('/tag/{tag}/tags','update_tag_tags')->name('update_tag_tags');
    //         Route::post('/tag/{tag}/codes','update_tag_codes')->name('update_tag_codes');
    //         Route::post('/tag/{tag}/statuses','update_tag_statuses')->name('update_tag_statuses');
    //         Route::post('/tag/{tag}/files','update_tag_files')->name('update_tag_files');
    //         Route::post('/tag/{tag}/comments','store_tag_comment')->name('store_tag_comment');
    //         Route::get('/tag/{tag}/codes','get_tag_codes')->name('get_tag_codes');
    //         Route::get('/tag/{tag}/tags','get_tag_tags')->name('get_tag_tags');
    //         Route::get('/tag/{tag}/statuses','get_tag_statuses')->name('get_tag_statuses');
    //         Route::get('/tag/{tag}/files','get_tag_files')->name('get_tag_files');
    //         Route::get('/tag/{tag}/comments','get_tag_comments')->name('get_tag_comments');
    //         Route::delete('/tag/{tag}/files/{file}','destroy_tag_file')->name('destroy_tag_file');
    //         Route::delete('/tag/{tag}/comments/{comment}','destroy_tag_comment')->name('destroy_tag_comment');
    //     });
    // });
});