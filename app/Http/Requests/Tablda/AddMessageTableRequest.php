<?php

namespace Vanguard\Http\Requests\Tablda;

use Illuminate\Foundation\Http\FormRequest;

class AddMessageTableRequest extends FormRequest
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
            'table_id' => 'required|integer|exists:tables,id',
            'to_user_id' => 'required|integer',
            'to_user_group_id' => 'nullable|integer',
            'message' => 'required|string'
        ];
    }
}
