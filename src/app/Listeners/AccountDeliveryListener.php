<?php

namespace App\Listeners;

use App\Enums\AccountStatusEnum;
use App\Events\OrderPaidEvent;
use App\Jobs\PrepareAccountDeliveryJob;
use App\Models\Account;

class AccountDeliveryListener
{
    public function handle(OrderPaidEvent $event): void
    {
        $order = $event->order;

        $order
            ->accounts()
            ->where([
                'status' => AccountStatusEnum::RESERVED,
            ])
            ->get()
            ->each(function (Account $account){
                PrepareAccountDeliveryJob::dispatch($account->id);
            });
    }
}
