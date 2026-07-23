<?php

namespace App\Services;

use App\Enums\AccountStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TransactionStatusEnum;
use App\Exceptions\OrderException;
use App\Exceptions\ProductDoesNotHaveStockException;
use App\Models\Account;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Payment\PaymentInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function addOrderItem(User $user,int $product_id)
    {
        $product = Product::find($product_id);
        if (!$product->hasAvailableStock()) {
            throw OrderException::causeOfUnavailableStock();
        }

        $order = Order::firstWhere([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::PENDING
        ]);

        try {
            DB::beginTransaction();

            if (!$order) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => OrderStatusEnum::PENDING
                ]);
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product_id
            ]);

            $account = Account::firstWhere('status',AccountStatusEnum::AVAILABLE);
            $account->lockForUpdate();

            $account->update([
                'order_id' => $order->id,
                'status' => AccountStatusEnum::RESERVED
            ]);

            Cache::set("X-Idempotency-Key:{$user->id}",true,5);

            $newAmount = $order->amount + $product->price;
            $order->update([
                'amount' => $newAmount
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
        }

        return $order;
    }

    public function proccessCallback(Order $order)
    {

    }
}
