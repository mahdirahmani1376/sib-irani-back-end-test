<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddOrderItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required','integer','exists:products,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
