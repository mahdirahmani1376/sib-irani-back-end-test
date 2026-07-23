<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\User;
use Database\Factories\AccountFactory;
use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_an_in_stock_product_to_an_order(): void
    {
        $customer = User::factory()->create();
        $product = ProductFactory::new()->create(['price' => 125000]);
        $account = AccountFactory::new()->create(['product_id' => $product->id]);

        Sanctum::actingAs($customer);

        $response = $this->postJson(route('order.add-item'), [
            'product_id' => $product->id,
        ], ['X-Idempotency-Key' => 'add-item-001']);

        $response->assertSuccessful();
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'status' => OrderStatusEnum::PENDING->value,
            'amount' => 125000,
        ]);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id]);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'order_id' => $this->orderIdFor($customer),
            'status' => AccountStatusEnum::RESERVED->value,
        ]);
    }

    private function orderIdFor(User $customer): int
    {
        return (int) $this->app['db']->table('orders')->where('user_id', $customer->id)->value('id');
    }

    public function test_the_same_idempotency_key_replays_the_original_order_response(): void
    {
        $customer = User::factory()->create();
        $product = ProductFactory::new()->create();
        AccountFactory::new()->create(['product_id' => $product->id,'status' => AccountStatusEnum::AVAILABLE]);
        Sanctum::actingAs($customer);

        $headers = ['X-Idempotency-Key' => 'same-request-001'];
        $first = $this->postJson(route('order.add-item'), ['product_id' => $product->id], $headers);
        $second = $this->postJson(route('order.add-item'), ['product_id' => $product->id], $headers);

        $first->assertSuccessful();
        $second->assertSuccessful();
        $this->assertSame($first->json(), $second->json());
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
    }

    public function test_only_one_customer_can_reserve_the_last_account(): void
    {
        $product = ProductFactory::new()->create();
        AccountFactory::new()->create(['product_id' => $product->id]);
        $firstCustomer = User::factory()->create();
        $secondCustomer = User::factory()->create();

        Sanctum::actingAs($firstCustomer);
        $this->postJson(route('order.add-item'), ['product_id' => $product->id], [
            'X-Idempotency-Key' => 'first-customer-001',
        ])->assertSuccessful();

        Sanctum::actingAs($secondCustomer);
        $secondResponse = $this->postJson(route('order.add-item'), ['product_id' => $product->id], [
            'X-Idempotency-Key' => 'second-customer-001',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseHas('accounts', ['status' => AccountStatusEnum::RESERVED->value]);
    }

}
