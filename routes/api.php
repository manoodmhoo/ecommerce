<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return new UserResource($request->user());
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

Route::get('/social/login/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('/social/login/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

Route::get('/carts', [CartController::class, 'store']);
Route::get('/carts/{cart}', [CartController::class, 'show']);
Route::post('/carts/{cart}', [CartController::class, 'addToCart']);
Route::delete('/carts/{cart}', [CartController::class, 'removeCart']);
Route::delete('/carts/{cart}/items/{item}', [CartController::class, 'removeCartItem']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::apiResource('/category', CategoryController::class);
    Route::apiResource('/product', ProductController::class);
    Route::get('/products', [ProductController::class, 'productCanBuy']);

    Route::post('/carts/{cart}/checkout', [CartController::class, 'checkout']);
});



