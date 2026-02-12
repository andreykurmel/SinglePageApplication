<?php

namespace Vanguard\Http\Requests\Tablda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserPayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'paypal_card' => 'required_without_all:order_id',
            'order_id' => 'required_without_all:paypal_card',
            'renew' => [
                'required',
                'string',
                Rule::in(['Yearly', 'Monthly'])
            ],
            'amount' => 'required|integer|min:0',
            'used_credit' => 'required|integer|min:0',
            'type' => 'required|string',
            'plan_code' => 'required|string|exists:plans,code',
            'all_addons' => 'required|array'
        ];
    }
}
