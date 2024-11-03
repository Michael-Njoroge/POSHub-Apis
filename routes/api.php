<?php

use App\Http\Controllers\ProductsController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [UsersController::class, 'register']);
    Route::post('/login', [UsersController::class, 'login']);
    Route::post('/verify-otp', [UsersController::class, 'verify_otp']);
    Route::post('/resend-otp', [UsersController::class, 'resend_otp']);
    Route::post('/forgot-password', [UsersController::class, 'forgot_password']);
    Route::post('/reset-password', [UsersController::class, 'reset_password']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/create-user', [UsersController::class, 'create_user']);
    Route::match(['put', 'patch'], '/change-password', [UsersController::class, 'change_password']);
    Route::get('/users', [UsersController::class, 'get_users']);
    Route::get('/users/{user}', [UsersController::class, 'get_user']);
    Route::get('/user-login/{user}', [UsersController::class, 'get_user_login']);
    Route::match(['put', 'patch'],'/users/{user}', [UsersController::class, 'update_user']);
    Route::post('/create-customer', [UsersController::class, 'create_company']);
    Route::get('/change-user-status/{user}', [UsersController::class, 'change_user_status']);
    Route::delete('/users/{user}', [UsersController::class, 'delete_user']);
    Route::get('/billers', [UsersController::class, 'get_billers']);
    Route::get('/customers', [UsersController::class, 'get_customers']);
    Route::get('/suppliers', [UsersController::class, 'get_suppliers']);

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductsController::class, 'get_products']);
        Route::post('/', [ProductsController::class, 'create_product']);
        Route::get('/{product}', [ProductsController::class, 'get_product']);
        Route::put('/{product}', [ProductsController::class, 'update_product']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [ProductsController::class, 'get_categories']);
        Route::post('/', [ProductsController::class, 'create_category']);
        Route::get('/{category}', [ProductsController::class, 'get_category']);
        Route::put('/{category}', [ProductsController::class, 'update_category']);
    });

    Route::prefix('statuses')->group(function () {
        Route::get('/', [ProductsController::class, 'get_product_statuses']);
        Route::post('/', [ProductsController::class, 'create_product_status']);
        Route::get('/{status}', [ProductsController::class, 'get_product_status']);
        Route::put('/{status}', [ProductsController::class, 'update_product_status']);
    });

    Route::prefix('warehouses')->group(function () {
        Route::get('/', [ProductsController::class, 'get_warehouses']);
        Route::post('/', [ProductsController::class, 'create_warehouse']);
        Route::get('/{warehouse}', [ProductsController::class, 'get_warehouse']);
        Route::put('/{warehouse}', [ProductsController::class, 'update_warehouse']);
        Route::get('/{warehouse}/products', [ProductsController::class, 'get_warehouse_products']);
    });
});