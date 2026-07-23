<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\Payment\PaymentInterface;
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
        $this->app->instance(PaymentInterface::class, new class implements PaymentInterface {
            public function getRedirectUrl(Order $order): string { return 'https://gateway.test/pay'; }
            public function processCallbackRequest(Order $order, array $data): bool { return true; }
        });

        Sanctum::actingAs($otherCustomer);

        $this->get("/api/orders/{$order->id}/checkout")->assertForbidden();
    }

    public function test_customer_can_checkout_their_own_order(): void
    {
        $customer = User::factory()->create();
        $order = OrderFactory::new()->create(['user_id' => $customer->id]);
        $this->app->instance(PaymentInterface::class, new class implements PaymentInterface {
            public function getRedirectUrl(Order $order): string { return 'https://gateway.test/pay'; }
            public function processCallbackRequest(Order $order, array $data): bool { return true; }
        });

        Sanctum::actingAs($customer);

        $this->get("/api/orders/{$order->id}/checkout")
            ->assertRedirect('https://gateway.test/pay');
    }
}
