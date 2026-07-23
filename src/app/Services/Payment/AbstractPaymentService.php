<?php

namespace App\Services\Payment;

use App\Models\Order;
use Str;

abstract class AbstractPaymentService
{
    public function getCallbackUrlForOrder(Order $order): string
    {
        return route('orders.callback',[
            'orderId' => $order->id
        ]);
    }
}
