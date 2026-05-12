<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

Route::apiResource('products', ProductController::class)
    ->only(['index', 'show'])
    ->parameters(['product' => 'id']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    Route::post('/products/{id}/restore', [ProductController::class, 'restore'])
        ->whereNumber('id')
        ->name('products.restore');

    Route::delete('/products/{id}/force', [ProductController::class, 'forceDestroy'])
        ->whereNumber('id')
        ->name('products.force-destroy');

    Route::apiResource('products', ProductController::class)
        ->only(['store', 'update', 'destroy'])
        ->parameters(['product' => 'id']);
});
