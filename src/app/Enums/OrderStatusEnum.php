<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';

    case PAID = 'paid';

    case DELIVERED = 'delivered';

    case FAILED = 'failed';

    case CANCELLED = 'cancelled';

    case REFUNDED = 'refunded';
}
