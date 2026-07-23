<?php

namespace App\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Events\Dispatchable;

class OrderPaidEvent
{
    use Dispatchable,Queueable;

    public function __construct()
    {
    }
}
