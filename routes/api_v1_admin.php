<?php

use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\ContactMessageController;
use App\Http\Controllers\Api\V1\Admin\HomeController;
use App\Http\Controllers\Api\V1\Admin\OwnerController;
use App\Http\Controllers\Api\V1\Admin\PlatformLinkController;
use App\Http\Controllers\Api\V1\Admin\ProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::apiResource('owners', OwnerController::class);
        Route::apiResource('projects', ProjectController::class);
        Route::post('/projects/{project}/gallery', [ProjectController::class, 'storeGallery']);
        Route::patch('/projects/{project}/gallery/{image}', [ProjectController::class, 'updateGallery']);
        Route::delete('/projects/{project}/gallery/{image}', [ProjectController::class, 'destroyGallery']);

        Route::get('/home', [HomeController::class, 'show']);
        Route::put('/home', [HomeController::class, 'update']);
        Route::post('/home/hero-gallery', [HomeController::class, 'storeHeroImage']);
        Route::delete('/home/hero-gallery/{image}', [HomeController::class, 'destroyHeroImage']);

        Route::get('/platform-links', [PlatformLinkController::class, 'index']);
        Route::put('/platform-links/{key}', [PlatformLinkController::class, 'upsert']);
        Route::patch('/platform-links/{platformLink}/toggle', [PlatformLinkController::class, 'toggle']);

        Route::get('/contact-messages', [ContactMessageController::class, 'index']);
        Route::get('/contact-messages/{contactMessage}', [ContactMessageController::class, 'show']);
        Route::patch('/contact-messages/{contactMessage}/status', [ContactMessageController::class, 'updateStatus']);
        Route::delete('/contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy']);
    });
});
