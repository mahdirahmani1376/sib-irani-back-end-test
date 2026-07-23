<?php

namespace App\Models;

use App\Enums\TransactionStatusEnum;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'order_id',
        'gateway_reference',
        'status',
        'amount',
    ];

    protected $casts = [
      'status' => TransactionStatusEnum::class
    ];
}
