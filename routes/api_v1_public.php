<?php

use App\Http\Controllers\Api\V1\Public\ContactController;
use App\Http\Controllers\Api\V1\Public\HomeController;
use App\Http\Controllers\Api\V1\Public\BlogController;
use App\Http\Controllers\Api\V1\Public\OwnerController;
use App\Http\Controllers\Api\V1\Public\PlatformLinkController;
use App\Http\Controllers\Api\V1\Public\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [HomeController::class, 'index']);
Route::get('/owners', [OwnerController::class, 'index']);
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{slug}', [BlogController::class, 'show']);
Route::get('/platforms', [PlatformLinkController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact');
