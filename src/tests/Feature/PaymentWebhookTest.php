<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Enums\TransactionStatusEnum;
use App\Jobs\PrepareAccountDeliveryJob;
use App\Models\Order;
use Database\Factories\AccountFactory;
use Database\Factories\OrderFactory;
use Database\Factories\TransactionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_gateway_webhook_marks_the_order_paid_and_queues_delivery(): void
    {
        config()->set('services.payment.saman.secret', 'test-payment-secret');
        Http::fake(['*' => Http::response(['ok' => true], 200)]);
        Queue::fake();

        $order = OrderFactory::new()->create();
        $account = AccountFactory::new()->create(['order_id' => $order->id]);
        $transaction = TransactionFactory::new()->create([
            'order_id' => $order->id,
            'status' => TransactionStatusEnum::PENDING->value,
            'amount' => -$order->amount,
        ]);
        $payload = ['ref_id' => $transaction->id, 'success' => true];

        $response = $this->postSignedJson("/api/orders/{$order->id}/callback", $payload);

        $response->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatusEnum::PAID->value]);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => TransactionStatusEnum::PAID->value]);
        Queue::assertPushed(PrepareAccountDeliveryJob::class, fn (PrepareAccountDeliveryJob $job) => $job->accountId === $account->id);
    }

    private function postSignedJson(string $uri, array $payload)
    {
        $content = json_encode($payload, JSON_THROW_ON_ERROR);

        return $this->call('POST', $uri, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => hash_hmac('sha256', $content, config('services.payment.saman.secret')),
        ], $content);
    }

    public function test_an_invalid_gateway_signature_is_rejected(): void
    {
        $order = OrderFactory::new()->create();
        $payload = ['ref_id' => 123, 'success' => true];

        $this->call('POST', "/api/orders/{$order->id}/callback", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => 'invalid-signature',
        ], json_encode($payload, JSON_THROW_ON_ERROR))->assertUnauthorized();
    }
}
