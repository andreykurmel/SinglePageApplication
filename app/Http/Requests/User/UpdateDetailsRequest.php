<?php

namespace Vanguard\Http\Requests\User;

use Illuminate\Validation\Rule;
use Vanguard\Http\Requests\Request;
use Vanguard\User;

class UpdateDetailsRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = \Route::input('user');
        return [
            'birthday' => 'nullable|date',
            'role_id' => 'required|exists:roles,id',
            'subdomain' => [
                'nullable',
                'string',
                Rule::unique('users', 'subdomain')->ignore($user ? $user->id : 0),
                'not_in:public'
            ],
        ];
    }
}
