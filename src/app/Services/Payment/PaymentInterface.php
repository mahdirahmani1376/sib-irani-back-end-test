<?php

namespace App\Services\Payment;

use App\Models\Order;

interface PaymentInterface
{
    public function getRedirectUrl(Order $order);

    public function processCallbackRequest(Order $order, array $data);
}
