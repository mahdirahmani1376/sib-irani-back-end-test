<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayCallbackRequest extends FormRequest
{
    public function rules(): array
    {
        $signature = $this->header('X-Signature');

        $expected = hash_hmac(
            'sha256',
            $this->getContent(),
            config('services.payment.saman.secret')
        );

        if (! hash_equals($expected, $signature)) {
            abort(401);
        }

        return [
            'ref_id' => ['required']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
