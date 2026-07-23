<?php

namespace App\Services\Payment;

use App\Models\Order;

interface PaymentInterface
{
    public function getRedirectUrl(Order $order);

    public function callback();

    public function acknowledgeSuccess(Order $order,array $data);
}
