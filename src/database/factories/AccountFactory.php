<?php

namespace Database\Factories;

use App\Enums\AccountStatusEnum;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Account> */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'credentials' => ['email' => fake()->safeEmail(), 'password' => fake()->password()],
            'status' => AccountStatusEnum::AVAILABLE->value,
            'product_id' => ProductFactory::new(),
            'order_id' => null,
        ];
    }
}
