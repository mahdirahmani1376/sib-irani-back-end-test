<?php

namespace App\Exceptions;

use Exception;

class OrderException extends Exception
{
    public static function causeOfUnavailableStock(): static
    {
        return new static('product has 0 stock available');
    }
}
