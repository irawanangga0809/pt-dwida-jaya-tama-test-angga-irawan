<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;

Route::aliasMiddleware('jwt.auth', JwtMiddleware::class);

// Primary Task
Route::post('/login', LoginController::class)->name('login');
Route::post('/users', [UserController::class, 'store']);

Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/users/{id}/posts', [UserController::class, 'getUserPosts']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::delete('/users/{id}', [UserController::class, 'delete']);
});


// Optional
Route::middleware(['jwt.auth'])->group(function () {
    Route::patch('/posts/{id}', [PostController::class, 'update']);
    Route::patch('/users/{id}',[UserController::class, 'update']);
    Route::post('/logout', LogoutController::class)->name('logout');
});



