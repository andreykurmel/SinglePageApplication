<?php

namespace Vanguard\Http\Requests\Tablda\Invitations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsersUpdateInvitationRequest extends FormRequest
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
            'invitation_id' => 'required|integer|exists:user_invitations,id',
            'fields' => 'required|array'
        ];
    }
}
