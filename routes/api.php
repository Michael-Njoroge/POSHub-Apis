<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//***********************ROUTES WITHOUT AUTH***************************//
//Auth
Route::prefix('auth')->group(function () {
    Route::post('/register', [UsersController::class, 'register']);
    Route::post('/login', [UsersController::class, 'login']);
    Route::post('/admin-login', [UsersController::class, 'admin_login']);
    Route::post('/verify-otp', [UsersController::class, 'verify_otp']);
    Route::post('/resend-otp', [UsersController::class, 'resend_otp']);
    Route::post('/forgot-password', [UsersController::class, 'forgot_password']);
    Route::post('/reset-password', [UsersController::class, 'reset_password']);
    Route::get('refresh-token', [UsersController::class, 'refresh_token']);
});

//Products
Route::prefix('products')->group(function () {
    Route::get('/', [ProductsController::class, 'get_products']);
    Route::get('/{product}', [ProductsController::class, 'get_product']);
});

//Product categories
Route::prefix('categories')->group(function () {
    Route::get('/', [ProductsController::class, 'get_categories']);
    Route::get('/{category}', [ProductsController::class, 'get_category']);
});

//Blogs
Route::prefix('blogs')->group(function () {
    Route::get('/', [BlogController::class, 'get_blogs']);
    Route::get('/{blog}', [BlogController::class, 'get_blog']);
});

//Blog categories
Route::prefix('blog-categories')->group(function () {
    Route::get('/', [BlogController::class, 'get_blog_categories']);
    Route::get('/{category}', [BlogController::class, 'get_blog_category']);
});

//Brands
Route::prefix('brands')->group(function () {
    Route::get('/', [BrandController::class, 'get_brands']);
    Route::get('/{brand}', [BrandController::class, 'get_brand']);
});

//Colors
Route::prefix('colors')->group(function () {
    Route::get('/', [ColorController::class, 'get_colors']);
    Route::get('/{color}', [ColorController::class, 'get_color']);
});

//Coupons
Route::prefix('coupons')->group(function () {
    Route::get('/', [CouponController::class, 'get_coupons']);
    Route::get('/{coupon}', [CouponController::class, 'get_coupon']);
});

//Media
Route::prefix('media')->group(function () {
    Route::post('/upload',[MediaController::class,'upload'])->name('products.upload');
});

//Payents
Route::prefix('payments')->group(function () {
    Route::post('/initiate', [MpesaController::class, 'initiatePayment'])->name('payment.initiate');
    Route::post('/confirmation', [MpesaController::class, 'handleCallback']);
    Route::post('/validation', [MpesaController::class, 'validation']);
});

//*****************************END OF ROUTES****************************//

//***********************ROUTES WITH AUTH*******************************//
Route::middleware(['auth:sanctum','active'])->group(function () {

    //Users
    Route::post('/create-user', [UsersController::class, 'create_user']);
    Route::get('/users', [UsersController::class, 'get_users']);
    Route::get('/users/{user}', [UsersController::class, 'get_user']);
    Route::match(['put', 'patch'], '/users/{user}', [UsersController::class, 'update_user']);
    Route::delete('/users/{user}', [UsersController::class, 'delete_user']);
    Route::put('/users/save-address/{user}',[UsersController::class,'saveAddress'])->name('save-address');
    Route::match(['put', 'patch'], '/change-password', [UsersController::class, 'change_password']);
    Route::match(['put', 'patch', 'post'], '/update-profile/{user}', [UsersController::class, 'update_profile_image']);
    
    //Auth
    Route::get('/user-login/{user}', [UsersController::class, 'get_user_login']);
    Route::put('/change-user-status/{user}', [UsersController::class, 'change_user_status']);
    Route::post('/logout/{user}', [UsersController::class, 'log_out']);

    //Companies
    Route::post('/create-customer', [UsersController::class, 'create_company']);
    Route::get('/billers', [UsersController::class, 'get_billers']);
    Route::get('/customers', [UsersController::class, 'get_customers']);
    Route::get('/suppliers', [UsersController::class, 'get_suppliers']);

    //Groups
    Route::get('/groups', [UsersController::class, 'get_groups']);

    //Categories
    Route::prefix('categories')->group(function () {
        Route::post('/', [ProductsController::class, 'create_category']);
        Route::put('/{category}', [ProductsController::class, 'update_category']);
        Route::delete('/{category}', [ProductsController::class, 'delete_category']);
    });

    //Statuses
    Route::prefix('statuses')->group(function () {
        Route::get('/', [ProductsController::class, 'get_product_statuses']);
        Route::post('/', [ProductsController::class, 'create_product_status']);
        Route::get('/{status}', [ProductsController::class, 'get_product_status']);
        Route::put('/{status}', [ProductsController::class, 'update_product_status']);
        Route::delete('/{status}', [ProductsController::class, 'delete_product_status']);
    });

     //Colors
     Route::prefix('colors')->group(function () {
        Route::get('/', [ProductsController::class, 'get_colors']);
        Route::post('/', [ProductsController::class, 'create_color']);
        Route::get('/{color}', [ProductsController::class, 'get_color']);
        Route::put('/{color}', [ProductsController::class, 'update_color']);
        Route::delete('/{color}', [ProductsController::class, 'delete_color']);
    });

    //Warehouses
    Route::prefix('warehouses')->group(function () {
        Route::get('/', [ProductsController::class, 'get_warehouses']);
        Route::post('/', [ProductsController::class, 'create_warehouse']);
        Route::get('/{warehouse}', [ProductsController::class, 'get_warehouse']);
        Route::put('/{warehouse}', [ProductsController::class, 'update_warehouse']);
        Route::delete('/{warehouse}', [ProductsController::class, 'delete_warehouse']);
        Route::get('/{warehouse}/products', [ProductsController::class, 'get_warehouse_products']);
        Route::put('/{warehouse}/status', [ProductsController::class, 'update_warehouse_status']);
    });

    //****************************ADMIN ROUTES**************************//

    Route::middleware(['auth:sanctum', 'admin', 'active'])->group(function () {
        //Products
        Route::prefix('products')->group(function () {
            Route::post('/', [ProductsController::class, 'create_product']);
            Route::put('/{product}', [ProductsController::class, 'update_product']);
        });

        //Blogs
        Route::prefix('blogs')->group(function () {
            Route::post('/', [BlogController::class, 'create_blog']);
            Route::put('/{blog}', [BlogController::class, 'update_blog']);
        });

        //Users
        Route::get('/users', [UsersController::class, 'get_users']);
        Route::get('/users/{user}', [UsersController::class, 'get_user']);
        Route::put('/users/{user}', [UsersController::class, 'update_user']);
        Route::delete('/users/{user}', [UsersController::class, 'delete_user']);
    });
});
