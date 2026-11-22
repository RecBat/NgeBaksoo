<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MenuController::class, 'index'])->name('menu');

// Keranjang
Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{index}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{index}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/promo', [CartController::class , 'promo'])->name('cart.promo');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Order
Route::get('/order/{order}', [OrderTrackingController::class, 'show'])->name('order.show');
Route::post('/order/{order}/review', [ReviewController::class, 'store'])->name('review.store');
