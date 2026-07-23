<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
        ];
    }
}
