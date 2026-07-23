<?php

use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/add-items', [OrderController::class,'addOrderItem'])->middleware('idempotent');
Route::get('/orders/{orderId}/checkout', [OrderController::class,'checkout'])->middleware('idempotent');
Route::get('/orders/{orderId}/callback', [OrderController::class,'callback'])->name('orders.callback');
