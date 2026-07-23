<?php

namespace App\Services\Payment;

use App\Models\Transaction;

abstract class AbstractPaymentService
{
    public function getCallbackUrlForOrder(Transaction $transaction): string
    {
        return route('transactions.callback',[
            'gateway_reference' => $transaction->gateway_reference
        ]);
    }
}
