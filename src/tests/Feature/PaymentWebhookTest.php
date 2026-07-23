<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TransactionStatusEnum;
use App\Jobs\PrepareAccountDeliveryJob;
use Database\Factories\AccountFactory;
use Database\Factories\OrderFactory;
use Database\Factories\TransactionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_gateway_webhook_marks_the_order_paid_and_queues_delivery(): void
    {
        Queue::fake();

        $order = OrderFactory::new()->create();
        $account = AccountFactory::new()->create([
            'order_id' => $order->id,
            'status' => AccountStatusEnum::RESERVED
        ]);

        $transaction = TransactionFactory::new()->create([
            'order_id' => $order->id,
            'status' => TransactionStatusEnum::PENDING->value,
            'amount' => -$order->amount,
            'gateway_reference' => Str::uuid()->toString()
        ]);


        $payload = [
            'ref_id' => $transaction->gateway_reference,
            'success' => true,
        ];

        $content = json_encode($payload, JSON_THROW_ON_ERROR);

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'X-Signature' => hash_hmac('sha256', $content, config('services.payment.saman.secret')),
        ];

        $response = $this->postJson(route('transactions.callback'),[
            'ref_id' => $transaction->gateway_reference,
            'success' => true
        ],$headers);

        $response->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatusEnum::PAID->value]);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => TransactionStatusEnum::PAID->value]);
//        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'status' => AccountStatusEnum::DELIVERED]);
        Queue::assertPushed(PrepareAccountDeliveryJob::class, fn (PrepareAccountDeliveryJob $job) => $job->accountId === $account->id);
    }

    public function test_an_invalid_gateway_signature_is_rejected(): void
    {
        $order = OrderFactory::new()->create();
        $transaction = TransactionFactory::new()->create([
           'order_id' => $order->id
        ]);

        $payload = [
            'ref_id' => $transaction->gateway_reference,
            'success' => true,
        ];

        $content = json_encode($payload, JSON_THROW_ON_ERROR);

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'X-Signature' => hash_hmac('sha256', $content, 'test'),
        ];

        $this->postJson(route('transactions.callback'),[
            'ref_id' => $transaction->gateway_reference,
            'success' => true
        ],$headers)->assertUnauthorized();
    }
}
