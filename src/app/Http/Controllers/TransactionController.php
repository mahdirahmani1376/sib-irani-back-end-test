<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentGatewayCallbackRequest;
use App\Services\Payment\PaymentInterface;

class TransactionController extends Controller
{
    public function callback(PaymentGatewayCallbackRequest $request,PaymentInterface $paymentGateway)
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-Signature');

        $expectedSignature = hash_hmac(
            'sha256',
            $rawBody,
            config('services.payment.saman.secret'),
        );

        if (! is_string($signature) || ! hash_equals($expectedSignature, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $result = $paymentGateway->processCallbackRequest($request->validated());

        return response()->json([
            'status' => $result ? 'success' : 'failed',
        ]);
    }

}
