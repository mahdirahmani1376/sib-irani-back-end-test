<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddOrderItemRequest;
use App\Http\Requests\PaymentGatewayCallbackRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\Payment\PaymentInterface;
use Illuminate\Support\Facades\Response;

class OrderController extends Controller
{
    public function addOrderItem(AddOrderItemRequest $request,OrderService $orderService)
    {
        $order = $orderService->addOrderItem(auth()->user(),$request->product_id);

        return Response::json(OrderResource::make($order));
    }

    public function checkout(Order $order,PaymentInterface $paymentGateway)
    {
        $redirectUrl = $paymentGateway->getRedirectUrl($order);

        return Response::json([
            'redirect_url' => $redirectUrl
        ]);
    }

    public function callback(PaymentGatewayCallbackRequest $request,Order $order,PaymentInterface $paymentGateway)
    {
        $result = $paymentGateway->processCallbackRequest($order,$request->validated());
        if ($result) {
            return Response::json([
                'status' => 'success',
                'message' => 'payment successfully made'
            ]);
        } else {
            return Response::json([
                'status' => 'failed',
                'message' => 'payment failed'
            ]);
        }


    }
}
