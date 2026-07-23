<?php

namespace App\Enums;

enum AccountStatusEnum :string
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case DELIVERED = 'delivered';
}
