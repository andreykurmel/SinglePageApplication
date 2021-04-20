<?php

namespace Vanguard\Http\Requests\Tablda;

use Illuminate\Foundation\Http\FormRequest;

class UsersUpdateRequest extends FormRequest
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
            'recurrent_pay' => 'required|integer|in:0,1',
            'pay_method' => 'required|string|in:stripe,paypal',
            'use_credit' => 'required|integer|in:0,1',
            'selected_card' => 'present|integer|nullable',
        ];
    }
}
