<?php

namespace Vanguard\Http\Requests\Tablda;

use Illuminate\Foundation\Http\FormRequest;

class Users2AddonsRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'addon_id' => 'required|integer|exists:addons,id'
        ];
    }
}
