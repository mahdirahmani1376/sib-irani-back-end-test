<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\IdempotencyMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(OrderController::class)->group(function () {
    Route::post('/add-items', 'addOrderItem')
        ->middleware('auth:sanctum')
        ->middleware(IdempotencyMiddleware::class)
        ->name('order.add-item');
    Route::get('/orders/{order}/checkout', 'checkout')
        ->middleware('auth:sanctum')
        ->middleware(IdempotencyMiddleware::class)
        ->middleware('can:checkout,order')
        ->name('orders.checkout');
});

Route::post('/transactions/callback', [TransactionController::class,'callback'])
    ->name('transactions.callback');
