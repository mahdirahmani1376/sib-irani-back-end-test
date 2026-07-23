<?php

namespace App\Http\Requests;

use App\Services\Payment\PaymentInterface;
use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayCallbackRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [];

        return array_merge(app(PaymentInterface::class)->rules(),$rules);
    }

    public function authorize(): bool
    {
        return true;
    }
}
