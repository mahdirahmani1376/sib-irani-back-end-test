<?php

namespace App\Exceptions;

use Exception;

class TransactionException extends Exception
{
    public static function causeOfGateWayError(): static
    {
        return new static('gateway fail to connect!');
    }
}
