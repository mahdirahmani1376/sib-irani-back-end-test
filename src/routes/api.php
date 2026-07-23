<?php

use App\Http\Controllers\OrderController;
use App\Http\Middleware\IdempotencyMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(OrderController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('/add-items', 'addOrderItem')
        ->middleware(IdempotencyMiddleware::class . ':add-items');
    Route::get('/orders/{orderId}/checkout', 'checkout')
        ->middleware(IdempotencyMiddleware::class . ':checkout');
    Route::get('/orders/{orderId}/callback', 'callback')->name('orders.callback');
});
