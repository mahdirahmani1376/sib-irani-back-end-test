<?php

namespace App\Listeners;

use App\Events\OrderPaidEvent;

class AccountDeliveryListener
{
    public function __construct()
    {
    }

    public function handle(OrderPaidEvent $event): void
    {

    }
}
