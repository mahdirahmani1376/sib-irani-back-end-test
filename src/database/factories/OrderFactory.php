<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new(),
            'status' => OrderStatusEnum::PENDING->value,
            'amount' => fake()->numberBetween(10_000, 500_000),
        ];
    }
}
