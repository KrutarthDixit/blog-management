<?php

use App\Http\Controllers\api\v1\BlogController;
use App\Http\Controllers\api\v1\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class);

Route::group([
    'middleware' => 'auth:sanctum',
    'controller' => BlogController::class,
], function () {
    Route::resource('blog', BlogController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::post('blog/{blog}/like', 'toggleLike');
});
