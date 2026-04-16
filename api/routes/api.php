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

use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\Admin\ShippingMethodController as AdminShippingMethodController;



Route::post('/register',       [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login',          [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink'])->middleware('throttle:3,1');
Route::post('/reset-password',  [ResetPasswordController::class, 'reset'])->middleware('throttle:5,1');

Route::get('/email/verify/{id}', [VerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::prefix('catalog')->group(function () {

    Route::get('/brands',        [BrandController::class, 'index']);
    Route::get('/brands/{slug}', [BrandController::class, 'show']);

    Route::get('/categories',        [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);

    Route::get('/products',        [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
});

Route::get('/shipping', [ShippingController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout',       [AuthController::class, 'logout']);
    Route::post('/email/resend', [VerificationController::class, 'resend']);
    Route::get('/email/status',  [VerificationController::class, 'status']);

    Route::delete('/cart', [\App\Http\Controllers\Api\CartController::class, 'clear']);
    Route::apiResource('cart', \App\Http\Controllers\Api\CartController::class)->except(['show']);

    Route::patch('/addresses/{address}/default', [\App\Http\Controllers\Api\AddressController::class, 'setDefault']);
    Route::apiResource('addresses', \App\Http\Controllers\Api\AddressController::class);

    Route::get('/shipping/calculate', [ShippingController::class, 'calculate']);

    Route::middleware('verified')->group(function () {
        Route::get('/zona-privada', function () {
            return response()->json([
                'message' => 'Acesso concedido: autenticado e email verificado!',
            ]);
        });
    });

    Route::middleware(['admin', 'verified'])->prefix('admin')->group(function () {
        Route::apiResource('brands', AdminBrandController::class);
        Route::apiResource('categories', AdminCategoryController::class);

        Route::patch('products/{product}/stock', [AdminProductController::class, 'updateStock']);
        Route::post('products/{product}/restore', [AdminProductController::class, 'restore'])->withTrashed();

        Route::patch('products/{product}/images/reorder', [\App\Http\Controllers\Api\Admin\ProductImageController::class, 'reorder']);
        Route::patch('products/{product}/images/{image}/primary', [\App\Http\Controllers\Api\Admin\ProductImageController::class, 'setPrimary']);
        Route::apiResource('products.images', \App\Http\Controllers\Api\Admin\ProductImageController::class)->only(['index', 'store', 'destroy']);

        Route::apiResource('products', AdminProductController::class);

        Route::apiResource('shipping-methods', AdminShippingMethodController::class);
    });
});
