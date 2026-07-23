<?php

namespace Database\Factories;

use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Transaction> */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'order_id' => OrderFactory::new(),
            'gateway' => 'saman',
            'status' => TransactionStatusEnum::PENDING->value,
            'amount' => -fake()->numberBetween(10_000, 500_000),
            'paid_at' => null,
        ];
    }
}
