<?php

namespace Vanguard\Http\Requests\Tablda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsersNextSubscriptionRequest extends FormRequest
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
            'renew' => [
                'required',
                'string',
                Rule::in(['Yearly', 'Monthly'])
            ],
            'plan_code' => 'required|string|exists:plans,code',
            'all_addons' => 'required|array'
        ];
    }
}
