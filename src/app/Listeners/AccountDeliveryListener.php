<?php

namespace App\Listeners;

use App\Enums\AccountStatusEnum;
use App\Events\OrderPaidEvent;
use App\Jobs\PrepareAccountDeliveryJob;
use App\Models\Account;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountDeliveryListener implements ShouldQueue
{
    public function __construct()
    {
    }

    public function handle(OrderPaidEvent $event): void
    {
        $order = $event->order;

        $order
            ->accounts()
            ->where([
                'status' => AccountStatusEnum::RESERVED,
                'user_id' => $order->user_id
            ])
            ->get()
            ->each(function (Account $account){
                PrepareAccountDeliveryJob::dispatch($account->id);
            });
    }
}
