<?php

use App\Models\User;
use Database\Factories\AccountFactory;
use Database\Factories\ProductFactory;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function (\App\Services\Payment\PaymentInterface $payment) {
    $product = ProductFactory::new()->create();
    \App\Models\Account::truncate();
    AccountFactory::new()->create(['product_id' => $product->id]);
    $firstCustomer = User::factory()->create();
    $secondCustomer = User::factory()->create();

    Auth::login($firstCustomer);
    Auth::login($secondCustomer);

    dump($firstCustomer->currentAccessToken(),$secondCustomer->currentAccessToken());


})->purpose('Display an inspiring quote');

Artisan::command('demo:postman', function () {
    $product = ProductFactory::new()->create();

    \App\Models\Account::truncate();
    // One account deliberately creates a last-item contention scenario.
    AccountFactory::new()->create([
        'product_id' => $product->id,
    ]);

    $firstCustomer = User::factory()->create();
    $secondCustomer = User::factory()->create();

    $firstToken = $firstCustomer
        ->createToken('postman-customer-one')
        ->plainTextToken;

    $secondToken = $secondCustomer
        ->createToken('postman-customer-two')
        ->plainTextToken;

    $this->table(
        ['Customer', 'Email', 'Bearer token'],
        [
            ['Customer one', $firstCustomer->email, $firstToken],
            ['Customer two', $secondCustomer->email, $secondToken],
        ],
    );

    $this->info("Product ID: {$product->id}");
})->purpose('Create two Postman customers and one in-stock product');

