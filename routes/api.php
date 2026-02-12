<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require __DIR__.'/api_v1_public.php';
    require __DIR__.'/api_v1_admin.php';
});
