<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\VerificationController;

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\Admin\BrandController as AdminBrandController;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;


// Rotas Públicas 

// Auth com tentativas limitadas
Route::post('/register',       [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login',          [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink'])->middleware('throttle:3,1');
Route::post('/reset-password',  [ResetPasswordController::class, 'reset'])->middleware('throttle:5,1');

Route::get('/email/verify/{id}', [VerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

// Rotas do Catálogo
Route::prefix('catalog')->group(function () {

    Route::get('/brands',        [BrandController::class, 'index']);
    Route::get('/brands/{slug}', [BrandController::class, 'show']);

    Route::get('/categories',        [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);

    Route::get('/products',        [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
});

// Rotas Autenticadas
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout',       [AuthController::class, 'logout']);
    Route::post('/email/resend', [VerificationController::class, 'resend']);
    Route::get('/email/status',  [VerificationController::class, 'status']);

    // Carrinho de Compras
    Route::delete('/cart', [\App\Http\Controllers\Api\CartController::class, 'clear']);
    Route::apiResource('cart', \App\Http\Controllers\Api\CartController::class);

    // Moradas do Cliente
    Route::apiResource('addresses', \App\Http\Controllers\Api\AddressController::class);

    // Rotas que exigem email verificado
    Route::middleware('verified')->group(function () {
        Route::get('/zona-privada', function () {
            return response()->json([
                'message' => 'Acesso concedido: autenticado e email verificado!',
            ]);
        });
    });

    // Rota do Admin
    Route::middleware(['admin', 'verified'])->prefix('admin')->group(function () {
        Route::apiResource('brands', AdminBrandController::class);
        Route::apiResource('categories', AdminCategoryController::class);
        Route::apiResource('products', AdminProductController::class);
    });
});
