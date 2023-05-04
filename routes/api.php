<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\DashboardController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login',[AuthController::class, 'login']);
Route::post('register',[AuthController::class, 'register']);

Route::middleware('auth:sanctum')->post('logout',[AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function() {

    Route::post('dashboard',[DashboardController::class, 'index']);

    Route::post('profile/update',[AuthController::class, 'profileUpdate']);
    Route::post('personalInfo/update',[AuthController::class, 'personalInfoUpdate']);
    Route::post('account/delete',[AuthController::class, 'AccountDelete']);

    Route::prefix('brand')->group(function () {
        Route::post('/',[BrandController::class, 'index']);
        Route::post('create',[BrandController::class,'create']);
        Route::post('update',[BrandController::class,'update']);
        Route::post('delete',[BrandController::class,'delete']);
        Route::post('restore',[BrandController::class,'restore']);
    });

    Route::prefix('category')->group(function () {
        Route::post('/',[CategoryController::class, 'index']);
        Route::post('create',[CategoryController::class,'create']);
        Route::post('update',[CategoryController::class,'update']);
        Route::post('delete',[CategoryController::class,'delete']);
        Route::post('restore',[CategoryController::class,'restore']);
    });

    Route::prefix('supplier')->group(function () {
        Route::post('/',[SupplierController::class, 'index']);
        Route::post('create',[SupplierController::class,'create']);
        Route::post('update',[SupplierController::class,'update']);
        Route::post('delete',[SupplierController::class,'delete']);
        Route::post('restore',[SupplierController::class,'restore']);
    });

    Route::prefix('product')->group(function () {
        Route::post('/',[ProductController::class, 'index']);
        Route::post('create',[ProductController::class,'create']);
        Route::post('update',[ProductController::class,'update']);
        Route::post('delete',[ProductController::class,'delete']);
        Route::post('restore',[ProductController::class,'restore']);
        Route::post('add/stock',[ProductController::class,'addStock']);
        Route::post('stock',[ProductController::class, 'stock']);
    });

    Route::post('order',[OrderController::class, 'order']);
    Route::post('order/history',[OrderController::class, 'orderHistory']);
});
