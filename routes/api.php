<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SpecialOfferController;
use App\Http\Controllers\DeliveryInfoController;
use App\Http\Controllers\PromoCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auths')->get('/users', function (Request $request) {
    return $request->user();
});

Route::post('register', [AuthController::class,'register']);
Route::get('approve/{id}', [AuthController::class,'approve']);
Route::post('login', [AuthController::class,'login']);

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('logout', [AuthController::class,'logout']);
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::post('direct', [AuthController::class,'direct']);
    Route::delete('delete_user/{id}',[UserController::class,'delete_user']);
    Route::put('update_user/{id}',[UserController::class,'update_user']);
    Route::post('update_password',[UserController::class,'changePassword']);
});

// Supplier Routes
Route::get('suppliers', [SupplierController::class, 'index']);
Route::post('suppliers', [SupplierController::class, 'store']);
Route::get('suppliers/{id}', [SupplierController::class, 'show']);
Route::put('suppliers/{id}', [SupplierController::class, 'update']);
Route::delete('suppliers/{id}', [SupplierController::class, 'destroy']);

// Order routes
Route::get('orders', [OrderController::class, 'index']);
Route::post('orders', [OrderController::class, 'store']);
Route::get('orders/{id}', [OrderController::class, 'show']);
Route::put('orders/{id}', [OrderController::class, 'update']);
Route::delete('orders/{id}', [OrderController::class, 'destroy']);

// OrderItem routes
Route::get('order-items', [OrderItemController::class, 'index']);
Route::post('order-items', [OrderItemController::class, 'store']);
//Route::get('order-items/{id}', [OrderItemController::class, 'show']);
Route::get('orders/items/{orderId}', [OrderController::class, 'getByOrderId']);
Route::put('order-items/{id}', [OrderItemController::class, 'update']);
Route::delete('order-items/{id}', [OrderItemController::class, 'delete']);

// Payment Routes
Route::get('payments', [PaymentController::class, 'index']);
Route::post('payments', [PaymentController::class, 'store']);
Route::get('payments/{id}', [PaymentController::class, 'show']);
Route::put('payments/{id}', [PaymentController::class, 'update']);
Route::delete('payments/{id}', [PaymentController::class, 'delete']);

// Product Routes
Route::get('products', [ProductController::class, 'index']);
Route::post('products', [ProductController::class, 'store']);
Route::get('products/{id}', [ProductController::class, 'show']);
Route::put('products/{id}', [ProductController::class, 'update']);
Route::delete('products/{id}', [ProductController::class, 'delete']);
Route::get('products/category/{categoryId}', [ProductController::class, 'getByCategoryId']);
Route::get('products/last/{x}', [ProductController::class, 'getLastProducts']);
Route::get('products/sorted', [ProductController::class, 'getAllProductsSorted']);

// SpecialOffers
Route::post('special-offers', [SpecialOfferController::class, 'insertOffer']); // API to insert a new offer
Route::delete('special-offers', [SpecialOfferController::class, 'deleteAllOffers']); // API to delete all offers
Route::delete('delete-special-offers/{id}', [SpecialOfferController::class, 'deleteOffersById']);
Route::get('special-offers', [SpecialOfferController::class, 'getAllOffers']); // API to get all offers
Route::get('special-offers/{id}', [SpecialOfferController::class, 'show']);

// Cart Routes
Route::post('cart/add', [CartController::class, 'addItem']);
Route::post('/cart/special-offer', [CartController::class, 'addSpecialOfferItem']);
Route::get('cart/{user_id}', [CartController::class, 'viewCart']);
Route::post('cart/checkout/{user_id}', [CartController::class, 'checkout']);
Route::delete('cart/item/{item_id}', [CartController::class, 'deleteItem']);
// Category Routes
Route::get('categories', [CategoryController::class, 'index']);
Route::post('categories', [CategoryController::class, 'store']);
Route::get('categories/{id}', [CategoryController::class, 'show']);
Route::put('categories/{id}', [CategoryController::class, 'update']);
Route::delete('categories/{id}', [CategoryController::class, 'delete']);

// // DeliveryInfo Routes
// Route::get('delivery-info', [DeliveryInfoController::class, 'index']);          // Get all records
// Route::post('delivery-info', [DeliveryInfoController::class, 'store']);         // Create a new record
// Route::get('delivery-info/{id}', [DeliveryInfoController::class, 'show']);      // Get a single record by ID
// Route::put('delivery-info/{id}', [DeliveryInfoController::class, 'update']);    // Update a record by ID
// Route::delete('delivery-info/{id}', [DeliveryInfoController::class, 'destroy']); // Delete a record by ID

// PromoCode Routes
Route::get('promo-codes', [PromoCodeController::class, 'index']);           // Get all promo codes
Route::post('promo-codes', [PromoCodeController::class, 'store']);           // Create a new promo code
Route::get('promo-codes/{id}', [PromoCodeController::class, 'show']);        // Get a single promo code by ID
Route::put('promo-codes/{id}', [PromoCodeController::class, 'update']);
Route::get('promo-code', [PromoCodeController::class, 'getPromoCodeDiscount']);      // Update a promo code by ID
Route::delete('promo-codes/{id}', [PromoCodeController::class, 'destroy']);  // Delete a promo code by ID