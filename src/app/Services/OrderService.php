<?php

namespace App\Services;

use App\Enums\AccountStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Exceptions\OrderException;
use App\Models\Account;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function addOrderItem(User $user, int $productId): Order
    {
        return DB::transaction(function () use ($user, $productId) {
            $product = Product::query()->find($productId);

            $account = Account::query()
                ->where('product_id', $product->id)
                ->where('status', AccountStatusEnum::AVAILABLE)
                ->lockForUpdate()
                ->first();

            if (! $account) {
                throw OrderException::causeOfUnavailableStock();
            }


            $order = Order::query()
                ->where('user_id', $user->id)
                ->where('status', OrderStatusEnum::PENDING)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => OrderStatusEnum::PENDING,
                    'amount' => 0,
                ]);
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $product->price,
            ]);

            $account->update([
                'order_id' => $order->id,
                'status' => AccountStatusEnum::RESERVED,
            ]);

            $order->increment('amount', $product->price);

            return $order->fresh();
        }, attempts: 3);
    }

}
