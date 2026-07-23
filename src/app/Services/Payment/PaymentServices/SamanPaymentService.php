<?php

namespace App\Services\Payment\PaymentServices;

use App\Enums\OrderStatusEnum;
use App\Enums\TransactionStatusEnum;
use App\Events\OrderPaidEvent;
use App\Exceptions\TransactionException;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Payment\AbstractPaymentService;
use App\Services\Payment\PaymentInterface;
use Illuminate\Support\Facades\Http;

class SamanPaymentService extends AbstractPaymentService implements PaymentInterface
{
    public function getRedirectUrl(Order $order)
    {
        $transaction = Transaction::create([
            'status' => TransactionStatusEnum::PENDING,
            'order_id' => $order->id,
            'gateway' => config('services.payment.saman.name'),
            'amount' => -$order->amount
        ]);

        $payload = [
            'amount' => $order->amount,
            'ref_id' => $transaction->id,
            'callback_url' => $this->getCallbackUrlForOrder($order),
            'secret' => config('services.payment.saman.secret')
        ];

        $response = Http::asJson()->post(config('services.payment.saman.gateway_url'), $payload);

        if ($response->ok()) {
            return $response->json('redirect_url');
        } else {
            $transaction->update([
                'status' => TransactionStatusEnum::FAILED
            ]);

            throw TransactionException::causeOfGateWayError();
        }
    }

    public function processCallbackRequest(Order $order, array $data)
    {
        $transaction = Transaction::firstWhere('id', $data['ref_id']);

        if ($data['success']) {
            $transaction->update([
                'status' => TransactionStatusEnum::PAID,
                'paid_at' => now()
                ]);

            $order->update([
                'status' => OrderStatusEnum::PAID
            ]);

            $response = Http::asJson()->post(config('services.payment.saman.callback_url'), [
                'ref_if' => $transaction->id
            ]);

            if ($response->ok()) {

                OrderPaidEvent::dispatch($order);

                return true;
            } else {
                return false;
            }

        } else {
            $transaction->update([
                'status' => TransactionStatusEnum::FAILED
            ]);

            $order->update([
                'status' => OrderStatusEnum::FAILED
            ]);

            Transaction::create([
                'order_id' => $order->id,
                'status' => OrderStatusEnum::REFUNDED,
                'gateway' => 'saman',
                'paid_at' => now(),
                'amount' => $order->amount
            ]);

            return false;
        }
    }
}
