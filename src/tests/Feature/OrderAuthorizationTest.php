<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Factories\OrderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_checkout_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $order = OrderFactory::new()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherCustomer);

        $headers = ['X-Idempotency-Key' => 'same-request-001'];

        $response = $this->getJson(route('orders.checkout',[
            'order' => $order->id
        ]),$headers);

        $response->assertForbidden();
    }

    public function test_customer_can_checkout_their_own_order(): void
    {
        $customer = User::factory()->create();
        $order = OrderFactory::new()->create(['user_id' => $customer->id]);

        Sanctum::actingAs($customer);
        $headers = ['X-Idempotency-Key' => 'same-request-001'];

        $response = $this->getJson(route('orders.checkout',[
            'order' => $order->id
        ]),$headers);

        $response->assertStatus(200);
    }
}
