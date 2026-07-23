<?php

namespace App\Models;

use App\Enums\AccountStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
    ];
    public function hasAvailableStock(): bool
    {
        return $this->accounts()->where([
            'status' => AccountStatusEnum::AVAILABLE
        ])->exists();
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'product_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }
}
