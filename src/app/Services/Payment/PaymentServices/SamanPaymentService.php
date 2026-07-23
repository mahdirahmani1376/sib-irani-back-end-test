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
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Log;

class SamanPaymentService extends AbstractPaymentService implements PaymentInterface
{
    public function getRedirectUrl(Order $order)
    {
        $transaction = Transaction::create([
            'status' => TransactionStatusEnum::PENDING,
            'order_id' => $order->id,
            'gateway_reference' => Str::uuid()->toString(),
            'amount' => -$order->amount
        ]);

        $payload = [
            'amount' => $order->amount,
            'ref_id' => $transaction->gateway_reference,
            'callback_url' => $this->getCallbackUrlForOrder($transaction),
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

    public function rules(): array
    {
        return [
            'ref_id' => ['required',Rule::exists('transactions','gateway_reference')
                ->where('status',TransactionStatusEnum::PENDING)
                ->where('gateway_reference',request('ref_id'))
            ],
            'success' => ['required']
        ];
    }

    public function processCallbackRequest(array $data)
    {
        $transaction = Transaction::where([
            'gateway_reference' => $data['ref_id'],
            'status' => TransactionStatusEnum::PENDING
                ])
            ->lockForUpdate()
            ->firstOrFail();

        $order = $transaction->order;

        if ($data['success']) {

            $response = false;
            try {
                $response = retry(3, function () use ($transaction) {
                    return Http::asJson()->post(config('services.payment.saman.callback_url'), [
                        'ref_if' => $transaction->gateway_reference
                    ]);
                }, 5);
            } catch (Exception $e) {
                Log::error('payment interface error',[
                    'providers' => 'saman',
                    'type' => 'callback url error',
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);
            }

            if ($response?->ok()) {
                DB::transaction(function () use ($order, $transaction) {
                    $transaction->update([
                        'status' => TransactionStatusEnum::PAID,
                        'paid_at' => now()
                    ]);

                    $order->update([
                        'status' => OrderStatusEnum::PAID
                    ]);

                }, 2);

                OrderPaidEvent::dispatch($order);

                return true;
            }
        }

        DB::transaction(function () use ($order, $transaction) {
            $transaction->update([
                'status' => TransactionStatusEnum::FAILED
            ]);

            $order->update([
                'status' => OrderStatusEnum::FAILED
            ]);

            Transaction::create([
                'order_id' => $order->id,
                'status' => OrderStatusEnum::REFUNDED,
                'gateway_reference' => null,
                'amount' => $order->amount
            ]);
        }, 2);


        return false;
    }
}
