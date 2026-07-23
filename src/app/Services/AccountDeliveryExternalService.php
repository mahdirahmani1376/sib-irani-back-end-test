<?php

namespace App\Services;

use App\Enums\AccountStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\Account;
use App\Models\Transaction;
use Exception;

class AccountDeliveryExternalService
{


    /**
     * this method simulates the response of an third-party api i just return a random true or false for simplicity
     * @return bool
     */
    public function deliverAccount(Account $account)
    {
        $rand = random_int(1, 3);

        if ($rand === 1) {
            throw new Exception('account delivery failed');
        }

        $account->update([
            'status' => AccountStatusEnum::DELIVERED,
        ]);
    }

    public function handleFailedAccountDelivery(Account $account): void
    {
        $account->order->update([
           'status' => OrderStatusEnum::FAILED
        ]);

        Transaction::create([
            'order_id' => $account->order_id,
            'status' => TransactionStatusEnum::REFUNDED,
            'amount' => $account->order->amount,
        ]);

        $account->update([
            'status' => AccountStatusEnum::AVAILABLE,
        ]);
    }


}
