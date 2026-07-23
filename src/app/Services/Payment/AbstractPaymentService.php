<?php

namespace App\Services\Payment;

use App\Models\Order;

abstract class AbstractPaymentService
{
    public function getCallbackUrlForOrder(Order $order): string
    {
        return route('orders.callback',[
            'order' => $order->id
        ]);
    }
}
