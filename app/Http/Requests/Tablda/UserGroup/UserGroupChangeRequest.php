<?php

namespace Vanguard\Http\Requests\Tablda\UserGroup;

use Illuminate\Foundation\Http\FormRequest;

class UserGroupChangeRequest extends FormRequest
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
            'user_group_id' => 'required|integer|exists:user_groups,id',
            'fields' => 'required|array',
        ];
    }
}
