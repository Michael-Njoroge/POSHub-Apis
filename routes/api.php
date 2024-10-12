<?php

use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [UsersController::class, 'login']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/create-user', [UsersController::class, 'create_user']);
    Route::get('/users', [UsersController::class, 'get_users']);
    Route::get('/users/{user}', [UsersController::class, 'get_user']);
    Route::put('/users/{user}', [UsersController::class, 'update_user']);

    Route::post('/create-customer', [UsersController::class, 'create_company']);

    Route::get('/billers', [UsersController::class, 'get_billers']);
    Route::get('/customers', [UsersController::class, 'get_customers']);
    Route::get('/suppliers', [UsersController::class, 'get_suppliers']);
});