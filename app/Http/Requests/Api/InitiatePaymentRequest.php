<?php

declare(strict_types = 1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'order_number' => 'required|string|exists:orders,order_number',

            // Kart bilgileri
            'card.holder_name' => 'required|string|max:100',
            'card.number' => 'required|string|regex:/^[0-9]{16}$/',
            'card.expire_month' => 'required|string|regex:/^(0[1-9]|1[0-2])$/',
            'card.expire_year' => 'required|string|regex:/^20[0-9]{2}$/',
            'card.cvc' => 'required|string|regex:/^[0-9]{3,4}$/',

            // Fatura adresi
            'billing_address.name' => 'required|string|max:100',
            'billing_address.address' => 'required|string|max:500',
            'billing_address.city' => 'required|string|max:50',
            'billing_address.postal_code' => 'required|string|max:10'
        ];
    }

    public function messages(): array
    {
        return [
            'order_number.required' => 'Sipariş numarası zorunludur',
            'order_number.exists' => 'Geçersiz sipariş numarası',

            'card.holder_name.required' => 'Kart sahibi adı zorunludur',
            'card.number.required' => 'Kart numarası zorunludur',
            'card.number.regex' => 'Geçersiz kart numarası formatı',
            'card.expire_month.required' => 'Son kullanma ayı zorunludur',
            'card.expire_month.regex' => 'Geçersiz ay formatı (01-12)',
            'card.expire_year.required' => 'Son kullanma yılı zorunludur',
            'card.expire_year.regex' => 'Geçersiz yıl formatı (20XX)',
            'card.cvc.required' => 'CVC kodu zorunludur',
            'card.cvc.regex' => 'Geçersiz CVC kodu',

            'billing_address.name.required' => 'Fatura adı zorunludur',
            'billing_address.address.required' => 'Fatura adresi zorunludur',
            'billing_address.city.required' => 'Şehir zorunludur',
            'billing_address.postal_code.required' => 'Posta kodu zorunludur'
        ];
    }
}
