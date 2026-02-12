<?php

namespace Vanguard\Http\Requests\User;

use Illuminate\Validation\Rule;
use Vanguard\Http\Requests\Request;
use Vanguard\User;

class UpdateProfileDetailsRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'birthday' => 'nullable|date',
            'subdomain' => [
                'nullable',
                'string',
                Rule::unique('users', 'subdomain')->ignore(auth()->id()),
                'not_in:public'
            ],
        ];
    }
}
